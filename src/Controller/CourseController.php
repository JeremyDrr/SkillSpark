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
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CourseController extends AbstractController
{

    private $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * @param $page
     * @param PaginationService $paginationService
     * @param StatsService $statsService
     * @return Response
     */
    #[Route('/courses/{page<\d+>?1}', name: 'courses_index')]
    public function index($page, PaginationService $paginationService, StatsService $statsService): Response
    {

        $trendingCourses = $statsService->getAmountCourse('DESC', 3);

        $paginationService->setEntityClass(Course::class)
            ->setPage($page)
            ->setLimit(12)
            ->setParamName('page');


        return $this->render('course/index.html.twig', [
            'pagination' => $paginationService,
            'trendingCourses' => $trendingCourses,
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param StripeService $stripeService
     * @return Response
     * @throws ApiErrorException
     */
    #[Route('/courses/create', name: 'courses_create')]
    #[IsGranted('ROLE_USER', message: 'You must be an authenticated SkillSpark user in order to be able to create a course')]
    public function create(Request $request, EntityManagerInterface $manager, StripeService $stripeService): Response
    {
        $course = new Course();

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($course->getChapters() as $chapter) {
                $chapter->setCourse($course);
                $manager->persist($chapter);
            }

            $course->setInstructor($this->getUser());

            // Create product and price on Stripe
            $stripeData = $stripeService->createProductAndPrice(
                $course->getTitle(),
                $course->getIntroduction(),
                $course->getPrice() * 100, // Convert the price to cents
                'eur',
                $course->isActive(),
            );

            $course->setStripeProductId($stripeData['product']->id);
            $course->setStripePriceId($stripeData['price']->id);

            $manager->persist($course);
            $manager->flush();

            $this->addFlash('success', 'You have successfully created the course ' . $course->getTitle());

            return $this->redirectToRoute('course_show', [
                'slug' => $course->getSlug()
            ]);
        }

        return $this->render('/course/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param $chapter
     * @param PaginationService $paginationService
     * @param Course $course
     * @return Response
     */
    #[Route('/course/{slug}/{chapter<\d+>?1}', name: 'course_show')]
    public function show($chapter, PaginationService $paginationService, Course $course, EntityManagerInterface $manager): Response
    {
        $paginationService->setEntityClass(Chapter::class)
            ->setLimit(1)
            ->setPage($chapter)
            ->setAdditionalParams(['slug' => $course->getSlug()])
            ->setFilters(['course' => $course])
            ->setParamName('chapter');

        if($course->getChapters()->count() > 0){
            $totalChapters = $manager->getRepository(Chapter::class)->count(['course' => $course]);
            $progress = ($chapter / $totalChapters) * 100;
        }else{
            $progress = 100;
        }


        return $this->render('course/show2.html.twig', [
            'pagination' => $paginationService,
            'course' => $course,
            'progress' => $progress,
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

        $this->addFlash('success', 'You have successfully deleted the course ' . $course->getTitle());

        return $this->redirectToRoute('courses_index');
    }

    /**
     * @param Request $request
     * @param Course $course
     * @param EntityManagerInterface $manager
     * @param StripeService $stripeService
     * @return Response
     * @throws ApiErrorException
     */
    #[Route('/course/{slug}/edit', name: 'course_edit')]
    public function edit(Request $request, Course $course, EntityManagerInterface $manager, StripeService $stripeService): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($course->getChapters() as $chapter) {
                $chapter->setCourse($course);
                $manager->persist($chapter);
            }

            // Update product and price on Stripe
            $stripeData = $stripeService->updateProductAndPrice(
                $course->getStripeProductId(),
                $course->getStripePriceId(),
                $course->getTitle(),
                $course->getIntroduction(),
                $course->getPrice() * 100, // Convert the price to cents
                'eur',
                $course->isActive(),
            );

            $course->setStripePriceId($stripeData['price']->id);

            $manager->persist($course);
            $manager->flush();

            $this->addFlash('success', 'You have successfully edited the course ' . $course->getTitle());

            return $this->redirectToRoute('course_show', [
                'slug' => $course->getSlug()
            ]);
        }

        return $this->render('/course/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
