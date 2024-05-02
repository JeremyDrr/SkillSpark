<?php

namespace App\Controller;

use App\Entity\Chapter;
use App\Entity\Course;
use App\Form\CourseEditType;
use App\Form\CourseType;
use App\Form\SignupType;
use App\Repository\CourseRepository;
use App\Services\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CourseController extends AbstractController
{
    /**
     * @param CourseRepository $courseRepository
     * @return Response
     */
    #[Route('/courses', name: 'courses_index')]
    public function index(CourseRepository $courseRepository): Response
    {

        return $this->render('course/index.html.twig', [
            'repository' => $courseRepository->findAll(),
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    #[Route('/courses/create', name: 'courses_create')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {

        $course = new Course();

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            foreach($course->getChapters() as $chapter) {
                $chapter->setCourse($course);
                $manager->persist($chapter);
            }

            $stripeService = new StripeService();
            $stripeProduct = $stripeService->createProduct($course);
            $course->setStripeProductId($stripeProduct->id);
            $stripePrice = $stripeService->createPrice($course);
            $course->setStripePriceId($stripePrice->id);

            $course->setInstructor($this->getUser());


            $manager->persist($course);
            $manager->flush();

            return $this->redirectToRoute('course_show', [
                'slug' => $course->getSlug()
            ]);
        }

        return $this->render('/course/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Course $course
     * @return Response
     */
    #[Route('/course/{slug}', name: 'course_show')]
    public function show(Course $course): Response
    {
        return $this->render('course/show.html.twig', [
            'course' => $course
        ]);
    }

    /**
     * @param Course $course
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/course/{slug}/delete', name: 'course_delete')]
    public function delete(Course $course, EntityManagerInterface $manager): Response
    {

        $manager->remove($course);
        $manager->flush();


        return $this->redirectToRoute('courses_index');
    }

    #[Route('/course/{slug}/edit', name: 'course_edit')]
    public function edit(Request $request, Course $course, EntityManagerInterface $manager){

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            foreach ($course->getChapters() as $chapter){
                $chapter->setCourse($course);
                $manager->persist($chapter);
            }

            $manager->persist($course);
            $manager->flush();

            return $this->redirectToRoute('course_show', [
                'slug' => $course->getSlug()
            ]);
        }

        return $this->render('/course/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}