<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class SecurityController extends AbstractController
{
    #[Route('/signup', name: 'signup')]
    public function signup(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer): Response
    {

        $user = new User();
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $hash = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Bienvenue sur Wonder !');
            $email = new TemplatedEmail();
            $email->to($user->getEmail())
                ->subject('Bienvenue sur wonder')
                ->htmlTemplate('@email_templates/welcome.html.twig')
                ->context([
                    'username' => $user->getFirstname()
                ]);

            $mailer->send($email);


            return $this->redirectToRoute('login');
        }



        return $this->render('security/signup.html.twig', [
            'form' => $userForm->createView(),
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est deja connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $username = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'error' => $error,
            'username' => $username
        ]);
    }
    #[Route('/logout', name: 'logout')]
    public function logout() {}


    #[Route('/reset-password-request', name: 'reset-password-request')]
    public function resetPasswordRequest(Request $request, UserRepository $userRepository)
    {

        $emailForm = $this->createFormBuilder()->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez renseigner votre email'
                ])
            ]
        ])->getForm();

        $emailForm->handleRequest($request);
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $email = $emailForm->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);
            if ($user) {
                $resetPassword = new ResetPassword();
                $resetPassword->setUser($user);
                $resetPassword->setExpiredAt(new \DateTimeImmutable('+2 hours'));
                $token = substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(30))), 0, 20);
                $resetPassword->setToken($token);
                dump($resetPassword);
            }
            $this->addFlash('success', 'Un email vous a été envoyé pour réinitialiser votre mot de passe');
        }

        return $this->render('security/reset-password-request.html.twig', [
            'form' => $emailForm->createView()
        ]);
    }
}
