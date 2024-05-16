<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUsersController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users_index')]
    public function index(UserRepository $repository): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $repository->findAll(),
        ]);
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
