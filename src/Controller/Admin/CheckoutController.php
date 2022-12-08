<?php

namespace App\Controller\Admin;

use App\Entity\Checkout;
use App\Repository\ProductRepository;
use App\Repository\CheckoutRepository;
use App\Repository\ProfileRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CheckoutController extends AbstractController
{
    #[Route('/admin/checkout/{id?}', name: 'app_admin_checkout_index', methods: ['GET'])]
    public function index(?int $id, CheckoutRepository $repos, ProfileRepository $profileRepository, Request $request): Response
    {
        $checkouts = $repos->findAll();

        if(!empty($id)){
            $client = $profileRepository->find($id);
            if($client){
                $checkouts = $repos->findBy(['createdBy' => $client]);
            }
        }
        
        $context = [
            'checkouts' => $checkouts,
        ];

        return $this->render('admin/checkout/index.html.twig', $context);
    }

    #[Route('/admin/checkout/show/{id}', name: 'app_admin_checkout_show', methods: ['GET'])]
    public function show(Checkout $checkout, ProductRepository $productRepository): Response
    {
        $dataPanier = [];
        $totalXof = 0;

        $panier = unserialize($checkout->getCheckout());

        foreach ($panier as $id => $quantity) {
            $product = $productRepository->find($id);
            $dataPanier[] = [
                "product" => $product,
                "quantity" => $quantity
            ];

            $totalXof += $product->getPriceXof() * $quantity;
        }
        
        $context = [
            'checkout' => $checkout,
            'dataPanier' => $dataPanier,
            'totalXof' => $totalXof,
        ];

        return $this->render('admin/checkout/show.html.twig', $context);
    }

   /*  #[Route('/admin/category/edit/{id}', name: 'app_admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Category $checkout, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryType::class, $checkout);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $checkout = $form->getData();

            $em->flush();

            $this->addFlash('success', "La catégorie < {$checkout->getName()} > a été modifiée avec succès");

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
    public function delete(Category $checkout, Request $request, EntityManagerInterface $em): Response
    {

        if($this->isCsrfTokenValid('category_deletion_'. $checkout->getId(), $request->request->get('csrf_token'))){
            $em->remove($checkout);
            $em->flush();

            $this->addFlash('success', "La catégorie < {$checkout->getName()} > a été suprimée avec succès");
        }
        

        return $this->redirectToRoute('app_admin_category_index');
    } 
    */
}
