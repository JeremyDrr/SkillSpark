<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @param Request $request
     * @param Course $course
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    #[Route('/admin/courses/{slug}/edit', name: 'admin_courses_edit')]
    public function edit(Request $request, Course $course, EntityManagerInterface $manager): RedirectResponse|Response
    {

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            foreach ($course->getChapters() as $chapter){
                $chapter->setCourse($course);
                $manager->persist($chapter);
            }

            $manager->persist($course);
            $manager->flush();

            return $this->redirectToRoute('admin_courses_index');

        }

        return $this->render('/admin/courses/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Course $course
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    #[Route('/admin/course/{slug}/delete', name: 'admin_courses_delete')]
    public function delete(Course $course, EntityManagerInterface $manager): RedirectResponse
    {

        $manager->remove($course);
        $manager->flush();

        return $this->redirectToRoute('admin_courses_index');

    }
}
