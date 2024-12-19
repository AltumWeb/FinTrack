<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransactionController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/transaction', name: 'app_transaction')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $repository = $this->entityManager->getRepository(Transaction::class);

        return $this->render('transaction/index.html.twig', [
            'controller_name' => 'TransactionController',
            'transactions' => $repository->findAll(),
        ]);
    }

    #[Route('/transaction/new', name: 'app_transaction_new')]
    public function new(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $transaction = new Transaction();

        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            $this->addFlash('success', 'La transaction a été ajoutée avec succès !');

            return $this->redirectToRoute('app_transaction');
        }

        return $this->render('transaction/new.html.twig', [
            'controller_name' => 'TransactionController',
            'form' => $form->createView(),
        ]);
    }
}
