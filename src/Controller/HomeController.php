<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [

        ]);
    }

    /**
     * @return Response
     */
    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('about.html.twig', [

        ]);
    }
}
