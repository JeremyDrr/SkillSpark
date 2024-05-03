<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use App\Service\StatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route('/', name: 'homepage')]
    public function index(CourseRepository $courseRepository, StatsService $statsService): Response
    {

        $trendingCourses = $statsService->getAmountCourse('DESC', 3);

        return $this->render('index.html.twig', [
            'courses' => $courseRepository->findAll(),
            'trendingCourses' => $trendingCourses,
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
