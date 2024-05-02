<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUsersController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users_index')]
    public function index(UserRepository $repository): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $repository->findAll(),
        ]);
    }
}
