<?php

namespace App\Controller;

use App\Entity\Course;
use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{

    /**
     * @param SessionInterface $session
     * @param CourseRepository $repository
     * @return Response
     */
    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, CourseRepository $repository): Response
    {
        $cart = $session->get('cart', []);

        $data = [];
        $total = 0;


        foreach ($cart as $slug => $quantity) {
            $course = $repository->findBySlug($slug);

            $data[] = [
                'course' => $course,
                'quantity' => $quantity
            ];

            $total += $course->getPrice() * $quantity;
        }
        return $this->render('cart/index.html.twig', compact('data', 'total'));

    }

    #[Route('/add/{slug}', name: 'add')]
    public function add(Course $course, SessionInterface $session): Response
    {
        $slug = $course->getSlug();

        // Get existing cart
        $cart = $session->get('cart', []);

        // Add course to the session cart if it's not already present
        if(empty($cart[$slug])){
            $cart[$slug] = 1;
        }
        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/remove/{slug}', name: 'remove')]
    public function remove(Course $course, SessionInterface $session): Response
    {
        $slug = $course->getSlug();

        // Get existing cart
        $cart = $session->get('cart', []);

        // Add course to the session cart if it's not already present
        if(!empty($cart[$slug])){
            unset($cart[$slug]);
        }
        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/empty', name: 'empty')]
    public function empty(SessionInterface $session): Response
    {

        // Get existing cart
        $session->remove('cart');

        return $this->redirectToRoute('cart_index');
    }
}