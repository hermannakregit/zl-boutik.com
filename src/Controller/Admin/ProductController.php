<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/admin/product', name: 'app_admin_product_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, ProductRepository $repos): Response
    {

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $product = $form->getData();

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', "Le produit < {$product->getName()} > a été ajoutée avec succès");

            return $this->redirectToRoute('app_admin_product_index', [], Response::HTTP_SEE_OTHER);
            
        }

        if($form->isSubmitted() && !$form->isValid()){
            $this->addFlash('warning', "La produit n'a pas été ajoutée, veuillez réessayer.");
        }

        $products = $repos->findAll();
        $context = [
            'product_form' => $form,
            'products' => $products,
        ];

        return $this->renderForm('admin/product/index.html.twig', $context);
    }

    #[Route('/admin/product/edit/{id}', name: 'app_admin_product_edit', methods: ['GET', 'POST'])]
    public function edit(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $product = $form->getData();

            $em->flush();

            $this->addFlash('success', "Le produit < {$product->getName()} > a été modifiée avec succès");

            return $this->redirectToRoute('app_admin_product_index', [], Response::HTTP_SEE_OTHER);
        }

        if($form->isSubmitted() && !$form->isValid()){
            $this->addFlash('warning', "Le produit n'a pas été modifiée, veuillez réessayer.");
        }
        
        $context = [
            'product_form' => $form,
        ];

        return $this->renderForm('admin/product/edit.html.twig', $context);
    }

    #[Route('/admin/product/delete/{id}', name: 'app_admin_product_delete', methods: 'POST')]
    public function delete(Product $product, Request $request, EntityManagerInterface $em): Response
    {

        if($this->isCsrfTokenValid('product_deletion_'. $product->getId(), $request->request->get('csrf_token'))){
            $em->remove($product);
            $em->flush();

            $this->addFlash('success', "Le produit < {$product->getName()} > a été suprimée avec succès");
        }
        

        return $this->redirectToRoute('app_admin_product_index');
    }
}
