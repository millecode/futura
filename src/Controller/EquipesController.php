<?php

namespace App\Controller;

use App\Entity\Equipes;
use App\Repository\EquipesRepository;
use App\Repository\ServicesRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\CoordonnerRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EquipesController extends AbstractController
{


    private $servicesRepo;
    private $programmmeRepo;
    private $equipeRepo;
    private $coordonnerRepo;

    public function __construct(ServicesRepository $servicesRepo, ProgrammeRepository $programmeRepo, CoordonnerRepository $coordonnerRepo, EquipesRepository $equipeRepo)
    {
        $this->servicesRepo = $servicesRepo;
        $this->programmmeRepo = $programmeRepo;
        $this->equipeRepo = $equipeRepo;
        $this->coordonnerRepo = $coordonnerRepo;
    }



    #[Route('/equipes', name: 'equipes')]
    public function index(): Response
    {
        //Liste de l'equipes.
        $equipes = $this->equipeRepo->findAll();

        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('equipes/equipe.html.twig', [
            'equipes' => $equipes,
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }



    #[Route('/equipes/equipe/{id}', name: 'voir_equipe')]
    public function Voirequipe(Equipes $equipe): Response
    {
        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('equipes/voirequipe.html.twig', [
            'equipe' => $equipe,
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }
}
