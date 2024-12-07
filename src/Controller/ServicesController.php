<?php

namespace App\Controller;

use App\Entity\Services;
use App\Repository\ServicesRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\CoordonnerRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ServicesController extends AbstractController
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

    #[Route('/services', name: 'services')]
    public function index(ServicesRepository $repoS, Request $request, PaginatorInterface $paginate): Response
    {
        //Affichage du liste
        $services = $paginate->paginate(
            $repoS->findAllWhitePagination(),
            $request->query->getInt('page', 1), /* page number */
            20 /* limit per page */
        );

        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);


        return $this->render('services/services.html.twig', [
            'services' => $services,
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }



    #[Route('/services/{id}/{slug}', name: 'voir_service')]
    public function VoirServices(Services $service, Request $request, ServicesRepository $repo): Response
    {
        $services = $repo->findAll();


        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('services/Voirservice.html.twig', [
            'service' => $service,
            'services' => $services,
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }
}
