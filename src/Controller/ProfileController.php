<?php

namespace App\Controller;

use App\Entity\Coordonner;
use DateTime;
use App\Entity\User;
use App\Form\CoordonnerType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\ServicesRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\CoordonnerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    private $servicesRepo;
    private $programmmeRepo;
    private $coordonnerRepo;

    public function __construct(ServicesRepository $servicesRepo, ProgrammeRepository $programmeRepo, CoordonnerRepository $coordonnerRepo)
    {
        $this->servicesRepo = $servicesRepo;
        $this->programmmeRepo = $programmeRepo;
        $this->coordonnerRepo = $coordonnerRepo;
    }


    #[Route('/admin/profile', name: 'profile')]
    #[Route('/admin/profile/modifier/{id}', name: 'modifier_profile')]
    public function profile(User $user = null, Request $request, EntityManagerInterface $manager, UserRepository $repoU, CoordonnerRepository $repoCo, UserPasswordHasherInterface $passwordHasher): Response
    {

        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);


        //Lister les users
        $users = $repoU->findAll();

        //Lister les coordonner
        $coordonnees = $repoCo->findAll();


        //Ajouter et Modifier un user
        if (empty($user)) {
            $user = new User;
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($hashedPassword);
            $user->setToken(1);
            $user->setDate(new DateTime());
            $manager->persist($user);
            $manager->flush();
            $this->addFlash("success", "Vous avez bien enregistré un utilisateur.");
            return $this->redirectToRoute('profile');
        }


        //Ajouter et modifier un coordonée
        $coordonner = new Coordonner;
        $formC = $this->createForm(CoordonnerType::class, $coordonner);
        $formC->handleRequest($request);

        if ($formC->isSubmitted() && $formC->isValid()) {
            $coordonner->setStatus(true);
            $manager->persist($coordonner);
            $manager->flush();
            $this->addFlash("success", "Vous avez bien enregistré une coordonnée.");
            return $this->redirectToRoute('profile');
        }



        return $this->render('admin/profile.html.twig', [
            'users' => $users,
            'coordonner' => $coordonnees,
            'formC' => $formC->createView(),
            'form' => $form->createView(),
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }


    //Supprimer un utilisateur
    #[Route('/admin/profile/user/supp/{id}', name: 'admin_users_supp')]
    public function SuppressionUser(User $user, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid("SUP" . $user->getId(), $request->get('_token'))) {
            $manager->remove($user);
            $manager->flush();
            $this->addFlash('danger', "Vous avez bien supprimer un utilisateur.");
            return $this->redirectToRoute('profile');
        }
    }




    //Supprimer un coordonnés
    #[Route('/admin/profile/coordonner/supp/{id}', name: 'admin_coordonner_supp')]
    public function SuppressionCoordonner(Coordonner $coordonner, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid("SUP" . $coordonner->getId(), $request->get('_token'))) {
            $manager->remove($coordonner);
            $manager->flush();
            $this->addFlash('danger', "VVous avez bien supprimé une coordonnée.");
            return $this->redirectToRoute('profile');
        }
    }


    //Activer/desactiver un coordonner
    #[Route('/admin/coordonner/activer/{id}', name: 'admin_coordonner_activer')]
    public function ActiverServices(Coordonner $coordonner, EntityManagerInterface $manager)
    {
        $coordonner->setStatus(($coordonner->isStatus()) ? false : true);
        $manager->persist($coordonner);
        $manager->flush();

        return new Response("true");
    }
}
