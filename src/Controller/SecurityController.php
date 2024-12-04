<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hash): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        if ($this ->getUser()) {
            return $this->redirectToRoute('app_start');
        }

        return $this->render('security/login.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/register', name: 'app_register')]
    public function registration(Request $request, UserPasswordHasherInterface $hash, EntityManagerInterface $entityManager) : Response 
    {
        $user = new User();
        $form = $this -> createForm(RegistrationFormType::class, $user);
        $form -> handleRequest($request);

        if ($form-> isSubmitted() && $form -> isValid()) {
            $user -> setPassword(
                $hash->hashPassword(
                    $user,
                    $form->get('Password')->getData()
                )
            );
            $entityManager -> persist($user);
            $entityManager -> flush();

            return $this-> redirectToRoute('app_login');
        }

        return $this -> render('security/register.html.twig', [
            'registrationForm' => $form -> createView()
        ]);    
    }
}
