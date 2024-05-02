<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCoursesController extends AbstractController
{
    #[Route('/admin/courses', name: 'admin_courses_index')]
    public function index(CourseRepository $repository): Response
    {
        return $this->render('admin/courses/index.html.twig', [
            'courses' => $repository->findAll(),
        ]);
    }
}
