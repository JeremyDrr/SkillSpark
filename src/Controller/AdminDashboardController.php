<?php

namespace App\Controller;

use App\Service\StatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $manager, StatsService $service): Response
    {

        $stats = $service->getStats();

        $bestCourses = $service->getCoursesStats('DESC');
        $worstCourses = $service->getCoursesStats('ASC');

        return $this->render('admin/dashboard/index.html.twig', [
            'stats'     => $stats,
            'bestCourses' => $bestCourses,
            'worstCourses' => $worstCourses,
        ]);
    }
}
