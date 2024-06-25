<?php

namespace App\Controller;

use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    #[Route('/', name: 'index')]
    #[IsGranted('ROLE_USER')]
    public function index(SessionInterface $session, CourseRepository $repository): Response
    {
        $cart = $session->get('cart', []);

        $data = [];
        $total = 0;

        foreach ($cart as $slug => $quantity) {
            $course = $repository->findOneBySlug($slug);

            $data[] = [
                'course' => $course,
                'quantity' => $quantity
            ];

            $total += $course->getPrice() * $quantity;
        }
        return $this->render('cart/index.html.twig', compact('data', 'total'));
    }

    #[Route('/add/{slug}', name: 'add')]
    #[IsGranted('ROLE_USER')]
    public function add(Course $course, SessionInterface $session, Request $request): RedirectResponse
    {
        $slug = $course->getSlug();

        if($course->getInstructor() == $this->getUser()){
            $referer = $request->headers->get('referer');
            $redirectUrl = $referer ? $referer . '#course-' . $course->getId() : $this->generateUrl('homepage');

            return $this->redirect($redirectUrl);
        }

        // Get existing cart
        $cart = $session->get('cart', []);

        // Add course to the session cart if it's not already present
        if (empty($cart[$slug])) {
            $cart[$slug] = 1;
        }
        $session->set('cart', $cart);

        $this->addFlash('success', 'You have added the course ' . $course->getTitle() . ' to the cart.');

        $referer = $request->headers->get('referer');
        $redirectUrl = $referer ? $referer . '#course-' . $course->getId() : $this->generateUrl('homepage');

        return $this->redirect($redirectUrl);

    }

    #[Route('/remove/{slug}', name: 'remove')]
    #[IsGranted('ROLE_USER')]
    public function remove(Course $course, SessionInterface $session): Response
    {
        $slug = $course->getSlug();

        // Get existing cart
        $cart = $session->get('cart', []);

        // Add course to the session cart if it's not already present
        if (!empty($cart[$slug])) {
            unset($cart[$slug]);
        }
        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/empty', name: 'empty')]
    #[IsGranted('ROLE_USER')]
    public function empty(SessionInterface $session): Response
    {
        // Get existing cart
        $session->remove('cart');

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/checkout', name: 'checkout')]
    #[IsGranted('ROLE_USER')]
    public function checkout(SessionInterface $session, CourseRepository $repository, StripeService $stripeService): Response
    {
        $cart = $session->get('cart', []);

        $lineItems = [];
        $total = 0;

        foreach ($cart as $slug => $quantity) {
            $course = $repository->findOneBySlug($slug);

            $lineItems[] = [
                'price' => $course->getStripePriceId(),
                'quantity' => $quantity,
            ];

            $total += $course->getPrice() * $quantity;
        }

        // Store the cart in the session
        $session->set('cart_for_checkout', $cart);

        $checkoutSession = $stripeService->createCheckoutSession($lineItems, $this->getUser()->getEmail());

        return $this->redirect($checkoutSession->url, 303);
    }

    #[Route('/success', name: 'success')]
    #[IsGranted('ROLE_USER')]
    public function success(SessionInterface $session, CourseRepository $repository, EntityManagerInterface $em): Response
    {
        $cart = $session->get('cart_for_checkout', []);
        $user = $this->getUser();

        foreach ($cart as $slug => $quantity) {
            $course = $repository->findOneBySlug($slug);
            $user->addCoursesFollowed($course);
        }

        $em->persist($user);
        $em->flush();

        // Clear the cart after successful checkout
        $session->remove('cart');
        $session->remove('cart_for_checkout');

        return $this->render('payment/success.html.twig');
    }

    #[Route('/cancel', name: 'cancel')]
    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig');
    }
}
