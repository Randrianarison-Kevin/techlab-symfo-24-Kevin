<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChatController extends AbstractController
{       
    #[Route('/start', name: 'app_start')]
    #[IsGranted('ROLE_USER')]
    public function startConversation(UserRepository $userRepository) : Response 
    {  
        // Recuperer tous les utilisateurs
        $allUser = $userRepository -> findAll(); 

        // Recuperer l'utillisateur connecter
        $currentUser = $this -> getUser();

        // Exclure l'utilisateur connecter
        $filterUser = array_filter($allUser, function($user) use ($currentUser){
            return $user !== $currentUser;
        });

        return $this->render('chat/start.html.twig',[
            'allUser' => $filterUser,
        ]);    
    }
}
