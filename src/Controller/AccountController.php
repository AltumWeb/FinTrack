<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\AccountType;
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
}
