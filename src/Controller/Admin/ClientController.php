<?php

namespace App\Controller\Admin;

use App\Repository\ProfileRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientController extends AbstractController
{
    #[Route('/admin/client', name: 'app_admin_client_index', methods: ['GET', 'POST'])]
    public function index(ProfileRepository $repos): Response
    {

        $profiles = $repos->findAll();
        
        $context = [
            'profiles' => $profiles,
        ];

        return $this->renderForm('admin/client/index.html.twig', $context);
    }

   /*  #[Route('/admin/category/edit/{id}', name: 'app_admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Category $profile, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryType::class, $profile);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $profile = $form->getData();

            $em->flush();

            $this->addFlash('success', "La catégorie < {$profile->getName()} > a été modifiée avec succès");

            return $this->redirectToRoute('app_admin_category_index', [], Response::HTTP_SEE_OTHER);
        }

        if($form->isSubmitted() && !$form->isValid()){
            $this->addFlash('warning', "La catégorie n'a pas été modifiée, veuillez réessayer.");
        }
        
        $context = [
            'category_form' => $form,
        ];

        return $this->renderForm('admin/category/edit.html.twig', $context);
    } */

   /*  #[Route('/admin/category/delete/{id}', name: 'app_admin_category_delete', methods: 'POST')]
    public function delete(Category $profile, Request $request, EntityManagerInterface $em): Response
    {

        if($this->isCsrfTokenValid('category_deletion_'. $profile->getId(), $request->request->get('csrf_token'))){
            $em->remove($profile);
            $em->flush();

            $this->addFlash('success', "La catégorie < {$profile->getName()} > a été suprimée avec succès");
        }
        

        return $this->redirectToRoute('app_admin_category_index');
    } */
}
