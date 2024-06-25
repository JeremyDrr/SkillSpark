<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUsersController extends AbstractController
{
    /**
     * @param UserRepository $repository
     * @return Response
     */
    #[Route('/admin/users', name: 'admin_users_index')]
    public function index(UserRepository $repository): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $repository->findAll(),
        ]);
    }

    #[Route('/admin/search-user', name: 'admin_search_user')]
    public function searchUser(Request $request, UserRepository $userRepository): JsonResponse
    {
        $searchTerm = $request->query->get('q', '');

        if (!$searchTerm) {
            return new JsonResponse([]);
        }

        $users = $userRepository->findByUsernameOrEmail($searchTerm);

        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => $user->getId(),
                'picture' => $user->getPicture(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'fullName' => $user->getFullName(),
                'slug' => $user->getSlug(),
                'roles' => $user->getRoles(),
                'verified' => $user->isVerified(),
                'coursesCreated' => $user->getCourses()->toArray(),
                'coursesFollowed' => $user->getCoursesFollowed()->toArray(),
                'warnings' => $user->getWarnings(),
                'banned' => $user->isBanned(),
                'showPath' => $this->generateUrl('user_show', ['slug' => $user->getSlug()]),
                'editPath' => $this->generateUrl('admin_users_edit', ['slug' => $user->getSlug()]),
                'deletePath' => $this->generateUrl('admin_users_delete', ['slug' => $user->getSlug()]),
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/user/{slug}/edit', name: 'admin_users_edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $manager): Response
    {

        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'You have successfully edited ' . $user->getFullName() . '\'s account.');

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/user/{slug}/delete', name: 'admin_users_delete')]
    public function delete(User $user, EntityManagerInterface $manager): Response
    {

        $manager->remove($user);
        $manager->flush();

        $this->addFlash('success', 'You have successfully deleted ' . $user->getFullName() . '\'s account.');

        return $this->redirectToRoute('admin_users_index');

    }

    /**
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    #[Route('/admin/user/{slug}/warn', name: 'admin_users_warn')]
    public function warn(User $user, EntityManagerInterface $manager): RedirectResponse
    {

        // Get the current amount of warnings
        $warnings = $user->getWarnings();

        if($warnings +1 >= 3){
            $user->setWarnings(3);
            $user->setBanned(true);
        }else{
            $user->setWarnings($user->getWarnings()+1);
        }

        $manager->persist($user);
        $manager->flush();


        return $this->redirectToRoute('admin_users_index');
    }

    /**
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    #[Route('/admin/user/{slug}/ban', name: 'admin_users_ban')]
    public function ban(User $user, EntityManagerInterface $manager): RedirectResponse
    {

        if($user->isBanned()){
            $user->setBanned(false);
        }else{
            $user->setBanned(true);
        }

        $manager->persist($user);
        $manager->flush();


        return $this->redirectToRoute('admin_users_index');
    }

    /**
     * @param User $user
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    #[Route('/admin/user/{slug}/resetwarnings', name: 'admin_users_resetwarnings')]
    public function resetwarnings(User $user, EntityManagerInterface $manager): RedirectResponse
    {

        $user->setWarnings(0);

        $manager->persist($user);
        $manager->flush();


        return $this->redirectToRoute('admin_users_index');
    }
}
