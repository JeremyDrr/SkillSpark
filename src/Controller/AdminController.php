<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {

        $error = $authenticationUtils->getLastAuthenticationError();
        $username = $authenticationUtils->getLastUsername();

        if($this->getUser() != null)
            return $this->redirectToRoute('admin_dashboard');

        return $this->render('admin/index.html.twig', [
            'hasError' => $error !== null,
            'username' => $username
        ]);
    }
}
