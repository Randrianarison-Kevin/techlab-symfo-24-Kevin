<?php
namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChatController extends AbstractController
{
    #[Route('/start', name: 'app_start')]
    #[IsGranted('ROLE_USER')]
    public function startConversation(UserRepository $userRepository) : Response 
    {  
        $allUser = $userRepository->findAll();
        $currentUser = $this->getUser();

        $filterUser = array_filter($allUser, function($user) use ($currentUser) {
            return $user !== $currentUser;
        });

        return $this->render('chat/start.html.twig', [
            'allUser' => $filterUser,
        ]);
    }

    #[Route('/chat/{id}', name: 'app_chat')]
    public function chat(HubInterface $hub, EntityManagerInterface $entityManager, MessageRepository $messageRepository, User $user, Request $request) : Response 
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new \LogicException('L\'utilisateur courant doit être une instance de User.');
        }

        $conversation = $messageRepository->createQueryBuilder('m')
            ->where('(m.user = :user AND m.receiver = :currentUser) OR (m.user = :currentUser AND m.receiver = :user)')
            ->setParameter('user', $user)
            ->setParameter('currentUser', $currentUser)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        if ($request->isMethod('POST')) {
            $content = $request->request->get('message');
            if ($content) {
                $message = new Message();
                $message->setContent($content);
                $message->setCreatedAt(new \DateTimeImmutable());
                $message->setUser($currentUser);
                $message->setReceiver($user);
                $entityManager->persist($message);
                $entityManager->flush();

                // Publier une mise à jour avec Mercure pour les deux utilisateurs
                $topics = [
                    sprintf('https://example.com/chat/%d', $currentUser->getId()),
                    sprintf('https://example.com/chat/%d', $user->getId())
                ];

                foreach ($topics as $topic) {
                    $update = new Update(
                        $topic,
                        json_encode(['message' => $content, 'sender' => $currentUser->getUserIdentifier()])
                    );
                    $hub->publish($update);
                }

                return $this->redirectToRoute('app_chat', ['id' => $user->getId()]);
            }
        }

        return $this->render('chat/chat.html.twig', [
            'user' => $user,
            'conversation' => $conversation
        ]);
    }
}
