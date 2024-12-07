<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailjetService;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Repository\ServicesRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\CoordonnerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, ServicesRepository $servicesRepo, ProgrammeRepository $programmeRepo, CoordonnerRepository $coordonnerRepo): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_logout');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();


        $servicess = $servicesRepo->findBy(['status' => true]);
        $programmess = $programmeRepo->findBy(['status' => true]);
        $coordonner = $coordonnerRepo->findBy(['status' => true]);

        return $this->render('security/login.html.twig', [
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess,
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }


    #[Route(path: '/mot-de-passe-oublier', name: 'mot-de-passe-oublier')]
    public function Motdepasseoublier(UserRepository $userRepository, Request $request, ServicesRepository $servicesRepo, ProgrammeRepository $programmeRepo, CoordonnerRepository $coordonnerRepo, EntityManagerInterface $manager, MailjetService $mailjetService): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_logout');
        }

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);
            if (!empty($user)) {
                $resetToken = bin2hex(random_bytes(32));
                $user->setToken($resetToken);
                $manager->flush();

                //Envoie d'email
                $recipientEmail = $email;
                $recipientName = 'Recipient Name';
                $subject = 'Réinitialisation du mot de passe';
                $textPart = 'This is the text version of the email.';
                $htmlPart = '<p>Pour réinitialiser votre mot de passe, veuillez cliquer sur le lien ci-dessous. :</p>' .
                    '<a href="' . $this->generateUrl('nouveau_password', ['token' => $resetToken], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL) . '">Réinitialiser le mot de passe</a>';

                $success = $mailjetService->sendEmail($recipientEmail, $recipientName, $subject, $textPart, $htmlPart);

                if ($success) {
                    $this->addFlash('success', 'Un e-mail de réinitialisation a été envoyé avec succès.');
                    return $this->redirectToRoute('mot-de-passe-oublier');
                }
            } else {
                $this->addFlash("danger", "L'e-mail n'existe pas. Veuillez vérifier votre adresse e-mail.");
            }
            return $this->redirectToRoute('mot-de-passe-oublier');
        }



        $servicess = $servicesRepo->findBy(['status' => true]);
        $programmess = $programmeRepo->findBy(['status' => true]);
        $coordonner = $coordonnerRepo->findBy(['status' => true]);

        return $this->render('security/mdpoublier.html.twig', [
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess,
        ]);
    }




    #[Route(path: '/mot-de-passe-oublier/reinitialisation/{token}', name: 'nouveau_password')]
    public function NouveauPassword(Request $request, $token, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, ServicesRepository $servicesRepo, ProgrammeRepository $programmeRepo, CoordonnerRepository $coordonnerRepo, EntityManagerInterface $manager): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_logout');
        }

        $user = $userRepository->findOneBy(['token' => $token]);

        if (!empty($user)) {
            if ($request->isMethod('POST')) {
                $newPassword = $request->request->get('password');
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                $user->setToken(1);
                $manager->flush();
                $this->addFlash('success', 'Votre mot de passe a bien été réinitialisé.');
                return $this->redirectToRoute('app_login');
            }
        } else {
            $this->addFlash('success', 'le lien n\'est pas valide.');
            return $this->redirectToRoute('mot-de-passe-oublier');
        }






        $servicess = $servicesRepo->findBy(['status' => true]);
        $programmess = $programmeRepo->findBy(['status' => true]);
        $coordonner = $coordonnerRepo->findBy(['status' => true]);

        return $this->render('security/nouveauMDP.html.twig', [
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }



    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
