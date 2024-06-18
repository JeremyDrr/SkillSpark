<?php

namespace App\Controller;

use App\Entity\Chapter;
use App\Entity\Course;
use App\Form\CourseEditType;
use App\Form\CourseType;
use App\Form\SignupType;
use App\Repository\CourseRepository;
use App\Service\PaginationService;
use App\Service\StatsService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CourseController extends AbstractController
{
    /**
     * @param CourseRepository $courseRepository
     * @param $page
     * @param PaginationService $paginationService
     * @return Response
     */
    #[Route('/courses/{page<\d+>?1}', name: 'courses_index')]
    public function index($page, PaginationService $paginationService, StatsService $statsService): Response
    {

        $trendingCourses = $statsService->getAmountCourse('DESC', 3);

        $paginationService->setEntityClass(Course::class)
            ->setPage($page)
            ->setLimit(18);

        return $this->render('course/index.html.twig', [
            'pagination' => $paginationService,
            'trendingCourses' => $trendingCourses,
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/courses/create', name: 'courses_create')]
    #[IsGranted('ROLE_USER', message: 'You must be an authentifiec SkillSpark user in order to be able to create a course')]
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

    /**
     * @param Request $request
     * @param Course $course
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     * @throws ApiErrorException
     */
    #[Route('/course/{slug}/edit', name: 'course_edit')]
    public function edit(Request $request, Course $course, EntityManagerInterface $manager): RedirectResponse|Response
    {

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            foreach ($course->getChapters() as $chapter){
                $chapter->setCourse($course);
                $manager->persist($chapter);
            }

            $stripeService = new StripeService();

            $stripeProduct = $stripeService->updatePrice($course);
            $course->setStripePriceId($stripeProduct);

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
