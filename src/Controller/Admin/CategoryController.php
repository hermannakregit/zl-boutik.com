<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'app_admin_category_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, CategoryRepository $repos): Response
    {

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $category = $form->getData();

            $em->persist($category);
            $em->flush();

            $this->addFlash('success', "La catégorie < {$category->getName()} > a été ajoutée avec succès");

            return $this->redirectToRoute('app_admin_category_index', [], Response::HTTP_SEE_OTHER);
            
        }

        if($form->isSubmitted() && !$form->isValid()){
            $this->addFlash('warning', "La catégorie n'a pas été ajoutée, veuillez réessayer.");
        }

        $categories = $repos->findAll();
        $context = [
            'category_form' => $form,
            'categories' => $categories,
        ];

        return $this->renderForm('admin/category/index.html.twig', $context);
    }

    #[Route('/admin/category/edit/{id}', name: 'app_admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $category = $form->getData();

            $em->flush();

            $this->addFlash('success', "La catégorie < {$category->getName()} > a été modifiée avec succès");

            return $this->redirectToRoute('app_admin_category_index', [], Response::HTTP_SEE_OTHER);
        }

        if($form->isSubmitted() && !$form->isValid()){
            $this->addFlash('warning', "La catégorie n'a pas été modifiée, veuillez réessayer.");
        }
        
        $context = [
            'category_form' => $form,
        ];

        return $this->renderForm('admin/category/edit.html.twig', $context);
    }

    #[Route('/admin/category/delete/{id}', name: 'app_admin_category_delete', methods: 'POST')]
    public function delete(Category $category, Request $request, EntityManagerInterface $em): Response
    {

        if($this->isCsrfTokenValid('category_deletion_'. $category->getId(), $request->request->get('csrf_token'))){
            $em->remove($category);
            $em->flush();

            $this->addFlash('success', "La catégorie < {$category->getName()} > a été suprimée avec succès");
        }
        

        return $this->redirectToRoute('app_admin_category_index');
    }
}


