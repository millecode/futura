<?php

namespace App\Controller;

use DateTime;
use App\Entity\Contacts;
use App\Form\ContactsType;
use App\Repository\ContactsRepository;
use App\Repository\ServicesRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\CoordonnerRepository;
use App\Service\MailjetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
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

    #[Route('/contact', name: 'contact')]
    public function index(Request $request, EntityManagerInterface $manager, MailjetService $mailjetService): Response
    {
        $contact = new Contacts();
        $form = $this->createForm(ContactsType::class, $contact);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $token = bin2hex(random_bytes(32));
            $contact->setToken($token);
            $contact->setDate(new DateTime());
            $contact->setStatusmail(false);
            $manager->persist($contact);
            $manager->flush();
            //Envoie d'email
            $recipientEmail = $contact->getEmail();
            $recipientName = $contact->getNom();
            $subject = 'Confirmation de votre email.';
            $textPart = '';
            $htmlPart = '<p>Merci de nous avoir contactés ! Veuillez confirmer votre adresse e-mail afin que nous puissions ouvrir une discussion en toute transparence." :</p>' .
                '<a href="' . $this->generateUrl('confirmation_contact', ['token' => $token], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL) . '">Confirmer votre email.</a>';

            $success = $mailjetService->sendEmail($recipientEmail, $recipientName, $subject, $textPart, $htmlPart);

            if ($success) {
                $this->addFlash('success', 'Merci pour votre contact. Un e-mail de confirmation a été envoyé. Veuillez le confirmer.');
                return $this->redirectToRoute('contact');
            }
        }
        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView(),
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }


    #[Route('/contact/confirmation-mail/{token}', name: 'confirmation_contact')]
    public function ConfirmationMail(Request $request, EntityManagerInterface $manager, $token, ContactsRepository $repoContacts): Response
    {
        $contacts = $repoContacts->findOneBy(['token' => $token]);
        if (empty($contacts)) {
            return $this->redirectToRoute('contact');
        } else {
            $contacts->setStatusmail(true);
            $manager->flush();
            $this->addFlash('success', "Votre e-mail a été confirmé avec succès. Nous prendrons contact avec vous dans les 24 heures pour poursuivre notre discussion.");
            return $this->redirectToRoute('contact');
        }


        $servicess = $this->servicesRepo->findBy(['status' => true]);
        $programmess = $this->programmmeRepo->findBy(['status' => true]);
        $coordonner = $this->coordonnerRepo->findBy(['status' => true]);

        return $this->render('contact/confirmationmail.html.twig', [
            'coordonners' => $coordonner,
            'servicess' => $servicess,
            'programmess' => $programmess
        ]);
    }
}
