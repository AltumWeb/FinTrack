<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\AccountType;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $repository = $this->entityManager->getRepository(Account::class);

        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
            'accounts' => $repository->findAll(),
        ]);
    }

    #[Route('/account/new', name: 'app_account_new')]
    public function new(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $account = new Account();
        $account->setUser($this->getUser());

        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($account);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le compte a été créé avec succès !');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/new.html.twig', [
            'controller_name' => 'AccountController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/account/edit/{id}', name: 'app_account_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, AccountRepository $repository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $accounts = $repository->find($id);

        if (!$accounts) {
            throw $this->createNotFoundException('Le compte n\'existe pas');
        }

        $form = $this->createForm(AccountType::class, $accounts);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Le compte a été modifié avec succès !');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/edit.html.twig', [
            'controller_name' => 'AccountController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/account/delete/{id}', name: 'app_account_delete', methods: ['GET', 'POST'])]
    public function delete(int $id, AccountRepository $repository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $accounts = $repository->find($id);

        if (!$accounts) {
            throw $this->createNotFoundException('Le compte n\'existe pas');
        }

        $this->entityManager->remove($accounts);
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            'Le compte a été supprimé avec succès !'
        );

        return $this->redirectToRoute('app_account');
    }
}
