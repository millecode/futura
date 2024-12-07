<?php

namespace App\Controller;

use App\Repository\CoordonnerRepository;
use App\Repository\PartenaireRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\ServicesRepository;
use App\Repository\TemoinageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private $servicesRepo;
    private $programmmeRepo;
    private $temoinageRepo;
    private $partenaireRepo;
    private $coordonnerRepo;
    public function __construct(ServicesRepository $servicesRepo, ProgrammeRepository $programmeRepo, TemoinageRepository $temoinageRepo, PartenaireRepository $partenaireRepo, CoordonnerRepository $coordonnerRepo)
    {
        $this->servicesRepo = $servicesRepo;
        $this->programmmeRepo = $programmeRepo;
        $this->temoinageRepo = $temoinageRepo;
        $this->partenaireRepo = $partenaireRepo;
        $this->coordonnerRepo = $coordonnerRepo;
    }





    #[Route('/', name: 'home')]
    public function index(): Response
    {
        //Liste des avis
        $temoinages = $this->temoinageRepo->findAll();
        //Liste des partenaires
        $partenaires = $this->partenaireRepo->findAll();


        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('home/index.html.twig', [
            'coordonners' => $coordonner,
            'temoinages' => $temoinages,
            'partenaires' => $partenaires,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }






    #[Route('/change_langue/{locale}', name: 'change_langue')]
    public function change_langue($locale, Request $request)
    {

        $request->getSession()->set('_locale', $locale);
        $referer = $request->headers->get('referer');
        // Si le referer n'existe pas, rediriger vers une page par défaut
        if (!$referer) {
            return $this->redirectToRoute('home'); // Remplacez 'home' par votre route par défaut
        }

        // Rediriger vers la page précédente
        return $this->redirect($referer);
    }
}
