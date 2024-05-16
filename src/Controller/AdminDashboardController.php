<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Service\StatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $manager, StatsService $service, CategorieRepository $categorieRepository): Response
    {

        $stats = $service->getStats();

        $bestCourses = $service->getCoursesStats('DESC');
        $worstCourses = $service->getCoursesStats('ASC');

        $categories = $categorieRepository->findAll();
        $catName = [];
        $catCount = [];
        $catColour = [];

        foreach($categories as $category){
            $catName[] = $category->getName();
            $catCount[] = count($category->getCourses());
            $catColour[] = $category->getColour();
        }

        return $this->render('admin/dashboard/index.html.twig', [
            'stats'     => $stats,
            'bestCourses' => $bestCourses,
            'worstCourses' => $worstCourses,
            'catName' => json_encode($catName),
            'catCount' => json_encode($catCount),
            'catColour' => json_encode($catColour)
        ]);
    }
}
