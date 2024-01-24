<?php

namespace App\Controller;

use App\Entity\Course;
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

            $stripeService = new StripeService();

            $stripeProduct = $stripeService->createProduct($course);
            $course->setStripeProductId($stripeProduct->id);

            $stripePrice = $stripeService->createPrice($course);
            $course->setStripePriceId($stripePrice->id);

            $course->setInstructor($this->getUser());

            $manager->persist($course);
            $manager->flush();

            return $this->redirectToRoute('homepage');
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
}
