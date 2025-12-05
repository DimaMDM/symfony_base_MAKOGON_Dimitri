<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Form\Flow\CandidateApplicationFlow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CandidatureController extends AbstractController
{
    #[Route('/apply', name: 'app_candidature')]
    public function index(Request $request, CandidateApplicationFlow $flow, EntityManagerInterface $entityManager): Response
    {
        $candidate = new Candidate();

        $flow->bind($candidate);

        // form of the current step
        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                // form for the next step
                $form = $flow->createForm();
            } else {
                // flow finished
                $candidate->setStatus('submitted');
                $entityManager->persist($candidate);
                $entityManager->flush();

                $flow->reset(); // remove step data from the session

                return $this->redirectToRoute('app_candidature_success', ['id' => $candidate->getId()]);
            }
        }

        return $this->render('candidature/index.html.twig', [
            'form' => $form->createView(),
            'flow' => $flow,
        ]);
    }

    #[Route('/success/{id}', name: 'app_candidature_success')]
    public function success(Candidate $candidate): Response
    {
        return $this->render('candidature/success.html.twig', [
            'candidate' => $candidate,
        ]);
    }
}