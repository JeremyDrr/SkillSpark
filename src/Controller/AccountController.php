<?php

namespace App\Controller;

use App\Entity\PasswordUpdate;
use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\AccountType;
use App\Form\ForgetPassword;
use App\Form\ForgetPasswordType;
use App\Form\PasswordUpdateType;
use App\Form\ResetPasswordType;
use App\Form\SignupType;
use App\Repository\UserRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Stripe;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
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
     * @param StripeService $stripeService
     * @return Response
     * @throws \Exception
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

    /**
     * @param Request $request
     * @param UserPasswordHasherInterface $encoder
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    #[Route('/account/password-update', name: 'account_password')]
    public function updatePassword(Request $request, UserPasswordHasherInterface $encoder, EntityManagerInterface $manager): RedirectResponse|Response
    {

        $user = $this->getUser();

        $passwordUpdate = new PasswordUpdate();

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if(!password_verify($passwordUpdate->getOldPassword(), $user->getPassword() )){
                $form->get('oldPassword')->addError(new FormError("The old password is incorrect"));
            }else{
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $encoder->hashPassword($user, $newPassword);
                $user->setPassword($hash);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'You have successfully updated your password.');

                return $this->redirectToRoute('homepage');
            }

        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/forget-password', name: 'user_forget_password')]
    public function forgetPassword(Request $request, UserRepository $repo, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $manager): Response {
        $form = $this->createForm(ForgetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $repo->findOneBy(['email' => $data['email']]);

            if ($user === null) {

                $form->addError(new FormError("The email address you entered matches with no user"));
                return $this->redirectToRoute('user_forget_password');
            }

            // Generate a unique token
            $token = $tokenGenerator->generateToken();

            try {
                $user->setResetToken($token);
                $manager->persist($user);
                $manager->flush();
            } catch (\Exception $e) {
                $this->addFlash('danger', 'An error occurred while sending reset password email: ' . $e->getMessage());
                return $this->redirectToRoute('login');
            }

            // Generate the password reset URL
            $url = $this->generateUrl('user_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            // Generate the email template
            $message = (new TemplatedEmail())
                ->from('no-reply@skillspark.com')
                ->to($user->getEmail())
                ->subject("Password Update")
                ->htmlTemplate('emails/forgetpassword.html.twig')
                ->context([
                    'firstName' => $user->getFirstName(),
                    'url' => $url,
                    'token' => $user->getResetToken()
                ]);

            // Send the email
            try {
                $mailer->send($message);
                $this->addFlash('success', 'An email has been sent to reset your password');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Failed to send email: ' . $e->getMessage());
            }

            return $this->redirectToRoute('login');
        }

        return $this->render('account/forgetpassword.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/reset-password/{token}', name: 'user_reset_password')]
    public function resetPassword(Request $request, string $token, UserPasswordHasherInterface $encoder, EntityManagerInterface $manager): Response{

        $resetPassword = new ResetPassword();

        $form = $this->createForm(ResetPasswordType::class, $resetPassword, ['tokenValue' => $token]);
        $form->handleRequest($request);

        $user = $manager->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if($user === null){
            $this->addFlash('danger', 'Invalid token');
            return $this->redirectToRoute('login');
        }

        if($form->isSubmitted() && $form->isValid()){

            $password = $resetPassword->getPassword();
            $hash = $encoder->hashPassword($user, $password);
            $user->setPassword($hash);

            $user->setResetToken(null);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Your password has been reset');
            return $this->redirectToRoute('login');
        }

        return $this->render('account/reset_password.html.twig', [
            'form' => $form->createView(),
        ]);

    }


}
