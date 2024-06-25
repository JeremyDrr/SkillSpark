<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Form\SignupType;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{
    /**
     * @param AuthenticationUtils $utils
     * @return Response
     */
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        if($this->getUser() != null)
            return $this->redirectToRoute('homepage');

        return $this->render('account/login.html.twig', [
            'hasError' => $error !== null,
            'username' => $username
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $encoder
     * @return Response
     */
    #[Route('/signup', name: 'signup')]
    public function signup(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $encoder, StripeService $stripeService): Response
    {

        if($this->getUser() != null)
            return $this->redirectToRoute('homepage');

        $user = new User();

        $form = $this->createForm(SignupType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $password = $encoder->hashPassword($user, $user->getPassword());
            $user->setPassword($password);

            // Create a connected Stripe account
            try {
                $stripeAccountId = $stripeService->createConnectedAccount();
                $user->setCreatorID($stripeAccountId);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Unable to create a Stripe account: ' . $e->getMessage());
                return $this->redirectToRoute('signup');
            }

            $manager->persist($user);
            $manager->flush();

            // Generate account link URLs
            $urls = $stripeService->generateAccountLinkUrls();
            $accountLinkUrl = $stripeService->generateAccountLink($stripeAccountId, $urls['return_url'], $urls['refresh_url']);

            $this->addFlash('success', 'Registration successful. You can now log in.');

            return $this->redirect($accountLinkUrl);



        }

        return $this->render('account/signup.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/user/{slug}/settings', name: 'user_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, EntityManagerInterface $manager): Response
    {

        $user = $this->getUser();

        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('user_show', [
                'slug' => $user->getSlug()
            ]);
        }

        return $this->render('/account/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param User $user
     * @return Response
     */
    #[Route('/user/{slug}', name: 'user_show')]
    public function show(User $user): Response
    {
        return $this->render('account/show.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @return void
     */
    #[Route('/logout', name: 'logout')]
    public function logout(){

    }
}
