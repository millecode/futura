<?php

namespace App\Controller;

use App\Entity\Programme;
use App\Repository\ServicesRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\CoordonnerRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProgrammeController extends AbstractController
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


    #[Route('/programmes', name: 'programmes')]
    public function index(ProgrammeRepository $repoP, Request $request, PaginatorInterface $paginate): Response
    {
        //Affichage du liste
        $programmes = $paginate->paginate(
            $repoP->findAllWhitePagination(),
            $request->query->getInt('page', 1), /* page number */
            20 /* limit per page */
        );

        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('programme/programme.html.twig', [
            'programmes' => $programmes,
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }


    #[Route('/programmes/{id}/{slug}', name: 'voir_programme')]
    public function VoirProgramme(Programme $programme, Request $request, ProgrammeRepository $repo): Response
    {
        $programmes = $repo->findAll();
        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('programme/Voirprogramme.html.twig', [
            'programme' => $programme,
            'programmes' => $programmes,
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }
}
