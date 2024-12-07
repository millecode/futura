<?php

namespace App\Controller;

use DateTime;
use App\Entity\Equipes;
use App\Entity\Contacts;
use App\Entity\Services;
use App\Form\EquipeType;
use App\Entity\Programme;
use App\Entity\Temoinage;
use App\Form\ServiceType;
use App\Entity\Actualiter;
use App\Entity\Partenaire;
use App\Form\ProgrammeType;
use App\Form\TemoinageType;
use App\Form\ActualiterType;
use App\Form\PartenaireType;
use App\Repository\EquipesRepository;
use App\Repository\ContactsRepository;
use App\Repository\ServicesRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\TemoinageRepository;
use App\Repository\ActualiterRepository;
use App\Repository\CoordonnerRepository;
use App\Repository\PartenaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
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



    // Accueil Admin
    #[Route('/admin', name: 'admin')]
    public function index(ServicesRepository $repoS, ProgrammeRepository $repoP, EquipesRepository $repoE, ContactsRepository $repo, PaginatorInterface $paginate, Request $request): Response
    {
        //Statistique

        $nbrService = $repoS->count();
        $nbrProgramme = $repoP->count();
        $nbrEquipe = $repoE->count();
        $nbrContacts = $repo->count();


        // Récupérer la recherche depuis la requête GET
        $searchName = $request->query->get('search_name', '');

        // Passer le critère de recherche au repository
        $query = $repo->findByNamePagination($searchName);

        // Appliquer la pagination
        $contacts = $paginate->paginate(
            $query,
            $request->query->getInt('page', 1), /* page number */
            20 /* limit per page */
        );
        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('admin/admin.html.twig', [
            'coordonners' => $coordonner,
            'contacts' => $contacts,
            'nbrService' => $nbrService,
            'nbrProgramme' => $nbrProgramme,
            'nbrEquipe' => $nbrEquipe,
            'nbrContact' => $nbrContacts,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }

    //Supprimer un contacts
    #[Route('/admin/contacts/supp/{id}', name: 'admin_contacts_supp')]
    public function SuppressionContacts(Contacts $contact, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid("SUP" . $contact->getId(), $request->get('_token'))) {
            $manager->remove($contact);
            $manager->flush();
            $this->addFlash('danger', "Vous avez bien supprimé un contact.");
            return $this->redirectToRoute('admin');
        }
    }

    //Voir un contacts
    #[Route('/admin/contacts/{id}', name: 'admin_contacts_voir')]
    public function VoirContacts(Contacts $contact): Response
    {
        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('admin/voirContacts.html.twig', [
            'contact' => $contact,
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }































    //Gestion de services
    #[Route('/admin/services/{id}', name: 'admin_service_modif')]
    #[Route('/admin/services', name: 'admin_service')]
    public function gestionservices(
        Services $service = null,
        Request $request,
        EntityManagerInterface $manager,
        ServicesRepository $repo,
        PaginatorInterface $paginate
    ): Response {
        //Affichage du liste
        $services = $paginate->paginate(
            $repo->findAllWhitePaginationAdmin(),
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );;

        //Ajouter et modifier un service

        if (!$service) {
            $service = new Services();
        }

        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slugtitre = preg_replace('/[^a-z0-9]+/', '-', trim(strtolower($service->getTitre())));
            $service->setSlug($slugtitre);
            $service->setDate(new DateTime());
            $service->setStatus(true);
            $manager->persist($service);
            $manager->flush();
            $this->addFlash("success", "Vous avez bien enregistré un service.");
            return $this->redirectToRoute('admin_service');
        }

        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('admin/gestionservice.html.twig', [
            'form' => $form->createView(),
            "services" => $services,
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }

    //Supprimer un service
    #[Route('/admin/services/supp/{id}', name: 'admin_service_supp')]
    public function SuppressionServices(Services $service, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid("SUP" . $service->getId(), $request->get('_token'))) {
            $manager->remove($service);
            $manager->flush();
            $this->addFlash('danger', "Vous avez bien supprimé un service.");
            return $this->redirectToRoute('admin_service');
        }
    }


    //Activer/desactiver un service
    #[Route('/admin/services/activer/{id}', name: 'admin_service_activer')]
    public function ActiverServices(Services $service, EntityManagerInterface $manager)
    {
        $service->setStatus(($service->isStatus()) ? false : true);
        $manager->persist($service);
        $manager->flush();

        return new Response("true");
    }
































    //Gestion de programme
    #[Route('/admin/programme', name: 'admin_programme')]
    #[Route('/admin/programme/modif/{id}', name: 'admin_programme_modif')]

    public function GestionsProgramme(Programme $programme = null, ProgrammeRepository $repo, Request $request, EntityManagerInterface $manager, PaginatorInterface $paginate): Response
    {
        //Liste des programmes
        $programmes = $paginate->paginate(
            $repo->findAllWhitePaginationAdmin(),
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );


        //Ajouter et modifier un programme

        if (!$programme) {
            $programme = new Programme();
        }

        $form = $this->createForm(ProgrammeType::class, $programme);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slugtitre = preg_replace('/[^a-z0-9]+/', '-', trim(strtolower($programme->getTitre())));
            $programme->setSlug($slugtitre);
            $programme->setDate(new DateTime());
            $programme->setStatus(true);
            $manager->persist($programme);
            $manager->flush();
            $this->addFlash("success", "Vous avez bien enregistré un programme.");
            return $this->redirectToRoute('admin_programme');
        }


        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('admin/gestionprogramme.html.twig', [
            'programmes' => $programmes,
            'form' => $form->createView(),
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }


    //Supprimer un programme
    #[Route('/admin/programme/supp/{id}', name: 'admin_programme_supp')]
    public function SuppressionProgramme(Programme $programme, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid("SUP" . $programme->getId(), $request->get('_token'))) {
            $manager->remove($programme);
            $manager->flush();
            $this->addFlash('danger', "Vous avez bien supprimé un programme.");
            return $this->redirectToRoute('admin_programme');
        }
    }


    //Activer/desactiver un programme
    #[Route('/admin/programme/activer/{id}', name: 'admin_programme_activer')]
    public function ActiverProgramme(Programme $programme, EntityManagerInterface $manager)
    {
        $programme->setStatus(($programme->isStatus()) ? false : true);
        $manager->persist($programme);
        $manager->flush();

        return new Response("true");
    }





































    //Gestion de l'équipe
    #[Route('/admin/equipe', name: 'admin_equipe')]
    #[Route('/admin/equipe/modif/{id}', name: 'admin_equipe_modif')]

    public function GestionsEquipes(Equipes $equipe = null, EquipesRepository $repo, Request $request, EntityManagerInterface $manager, PaginatorInterface $paginate): Response
    {
        //Liste des equipes
        $equipes = $paginate->paginate(
            $repo->findAllWhitePagination(),
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );


        //Ajouter et modifier un equipes

        if (!$equipe) {
            $equipe = new Equipes();
        }

        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $equipe->setDate(new DateTime());
            $manager->persist($equipe);
            $manager->flush();
            $this->addFlash("success", "Vous avez bien enregistré un personnel.");
            return $this->redirectToRoute('admin_equipe');
        }

        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('admin/gestionequipe.html.twig', [
            'equipes' => $equipes,
            'form' => $form->createView(),
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }


    //Supprimer un equipes
    #[Route('/admin/equipe/supp/{id}', name: 'admin_equipes_supp')]
    public function SuppressionEquipe(Equipes $equipe, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid("SUP" . $equipe->getId(), $request->get('_token'))) {
            $manager->remove($equipe);
            $manager->flush();
            $this->addFlash('danger', "Vous avez bien supprimé un personnel.");
            return $this->redirectToRoute('admin_equipe');
        }
    }






































    //Gestion de l'actualiter
    #[Route('/admin/actualiter', name: 'admin_actualiter')]
    #[Route('/admin/actualiter/modif/{id}', name: 'admin_actualiter_modif')]

    public function GestionsActualiter(Actualiter $actualiter = null, ActualiterRepository $repo, Request $request, EntityManagerInterface $manager, PaginatorInterface $paginate): Response
    {
        //Liste des actualiter
        $actualiters = $paginate->paginate(
            $repo->findAllWhitePagination(),
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );


        //Ajouter et modifier une Actualiter

        if (!$actualiter) {
            $actualiter = new Actualiter();
        }

        $form = $this->createForm(ActualiterType::class, $actualiter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slugtitre = preg_replace('/[^a-z0-9]+/', '-', trim(strtolower($actualiter->getTitre())));
            $actualiter->setSlug($slugtitre);
            $actualiter->setDate(new DateTime());
            $actualiter->setStatus(true);
            $manager->persist($actualiter);
            $manager->flush();
            $this->addFlash("success", "Vous avez bien enregistré un programme.");
            return $this->redirectToRoute('admin_actualiter');
        }

        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('admin/gestionActualite.html.twig', [
            'actualiter' => $actualiters,
            'form' => $form->createView(),
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }


    //Supprimer une actualiter
    #[Route('/admin/actualiter/supp/{id}', name: 'admin_actualiter_supp')]
    public function SuppressionActualiter(Actualiter $actualiter, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid("SUP" . $actualiter->getId(), $request->get('_token'))) {
            $manager->remove($actualiter);
            $manager->flush();
            $this->addFlash('danger', "Vous avez bien supprimé une actualité.");
            return $this->redirectToRoute('admin_actualiter');
        }
    }

































    //Gestion de partenaire
    #[Route('/admin/parametre', name: 'admin_parametre')]
    #[Route('/admin/parametre/partenaire/modif/{id}', name: 'admin_partenaire_modif')]
    #[Route('/admin/parametre/temoinage/modif/{id}', name: 'admin_temoinage_modif')]
    public function GestionsPartenaire(Partenaire $partenaire = null, PartenaireRepository $repoP, Request $request, EntityManagerInterface $manager, PaginatorInterface $paginate, Temoinage $temoinage = null, TemoinageRepository $repoT): Response
    {
        //Liste des partenaire
        $partenaires = $paginate->paginate(
            $repoP->findAllWhitePaginationAdmin(),
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        //Ajouter et modifier un partenaire

        if (!$partenaire) {
            $partenaire = new Partenaire();
        }

        $form = $this->createForm(PartenaireType::class, $partenaire);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slugtitre = preg_replace('/[^a-z0-9]+/', '-', trim(strtolower($partenaire->getNom())));
            $partenaire->setSlug($slugtitre);
            $partenaire->setDate(new DateTime());
            $manager->persist($partenaire);
            $manager->flush();
            $this->addFlash("success", "Vous avez bien enregistré un partenaire.");
            return $this->redirectToRoute('admin_parametre');
        }


        //Liste des Temoinages
        $temoinages = $paginate->paginate(
            $repoT->findAllWhitePaginationAdmin(),
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        //Ajouter et modifier Un temoinage
        if (!$temoinage) {
            $temoinage = new Temoinage();
        }
        $formT = $this->createForm(TemoinageType::class, $temoinage);
        $formT->handleRequest($request);
        if ($formT->isSubmitted() && $formT->isValid()) {
            $temoinage->setDate(new DateTime());
            $manager->persist($temoinage);
            $manager->flush();
            $this->addFlash("success", "VVous avez bien enregistré un avis.");
            return $this->redirectToRoute('admin_parametre');
        }



        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('admin/gestionpartenaire.html.twig', [
            'partenaires' => $partenaires,
            'temoinages' => $temoinages,
            'form' => $form->createView(),
            'formT' => $formT->createView(),
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }


    //Supprimer un partenaire
    #[Route('/admin/partenaire/supp/{id}', name: 'admin_partenaire_supp')]
    public function SuppressionPartenaire(Partenaire $partenaire, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid("SUP" . $partenaire->getId(), $request->get('_token'))) {
            $manager->remove($partenaire);
            $manager->flush();
            $this->addFlash('danger', "Vous avez bien supprimé un partenaire.");
            return $this->redirectToRoute('admin_parametre');
        }
    }


    //Supprimer un temoinage
    #[Route('/admin/temoinage/supp/{id}', name: 'admin_temoinage_supp')]
    public function SuppressionTemoinage(Temoinage $temoinage, Request $request, EntityManagerInterface $manager): Response
    {
        if ($this->isCsrfTokenValid("SUP" . $temoinage->getId(), $request->get('_token'))) {
            $manager->remove($temoinage);
            $manager->flush();
            $this->addFlash('danger', "Vous avez bien supprimé un avis.");
            return $this->redirectToRoute('admin_parametre');
        }
    }
}
