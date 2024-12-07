<?php

namespace App\Controller;

use App\Entity\Actualiter;
use App\Repository\ServicesRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\ActualiterRepository;
use App\Repository\CoordonnerRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ActualiterController extends AbstractController
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


    #[Route('/actualiter', name: 'actualiter')]
    public function index(ActualiterRepository $repoA, Request $request, PaginatorInterface $paginate): Response
    {
        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        $actualiters = $paginate->paginate(
            $repoA->findAllWhitePagination(),
            $request->query->getInt('page', 1), /* page number */
            15 /* limit per page */
        );;

        return $this->render('actualiter/actualiter.html.twig', [
            'actualiters' => $actualiters,
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }

    #[Route('/actualiter/{id}/{slug}', name: 'voir_actualiter')]
    public function VoirServices(Actualiter $actualiter, Request $request): Response
    {



        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('actualiter/Voiractualiter.html.twig', [
            'actualiter' => $actualiter,
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }
}
