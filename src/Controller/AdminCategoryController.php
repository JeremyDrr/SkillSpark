<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCategoryController extends AbstractController
{
    #[Route('/admin/categories', name: 'admin_categories_index')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('admin/category/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/admin/categories/create', name: 'admin_categories_create')]
    public function create(Request $request, EntityManagerInterface $manager): Response
    {

        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('admin_categories_index');
        }


        return $this->render('admin/category/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Category $category
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    #[Route('/admin/category/{name}/edit', name: 'admin_categories_edit')]
    public function edit(Category $category, Request $request, EntityManagerInterface $manager): RedirectResponse|Response
    {

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->render('admin/category/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/category/{name}/delete', name: 'admin_categories_delete')]
    public function delete(Category $category, EntityManagerInterface $manager){

        $manager->remove($category);
        $manager->flush();

        return $this->redirectToRoute('admin_categories_index');
    }
}
