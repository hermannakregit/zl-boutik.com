<?php

namespace App\Controller\Admin;

use App\Entity\Image;
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

            // traitement des images
            $images = $form->get('images')->getData();
            //on boucle sur les images
            foreach ($images as $image) {
               // on génère un nouveau nom de fichier
               $fichier = md5(uniqid()) . '.' . $image->guessExtension();

               // on copie le fichier dans le dossier  (directory)
               $image->move(
                $this->getParameter('images_upload_directory'),
                $fichier
               );

               // on stocke l'image le nom de l'image dans la base de donnée
               $img = new Image();
               $img->setName($fichier);
               $product->addImage($img);

            }

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

            // traitement des images
            $images = $form->get('images')->getData();
            //on boucle sur les images
            foreach ($images as $image) {
               // on génère un nouveau nom de fichier
               $fichier = md5(uniqid()) . '.' . $image->guessExtension();

               // on copie le fichier dans le dossier  (directory)
               $image->move(
                $this->getParameter('images_upload_directory'),
                $fichier
               );

               // on stocke l'image le nom de l'image dans la base de donnée
               $img = new Image();
               $img->setName($fichier);
               $product->addImage($img);

            }

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
            'product' => $product,
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

    #[Route('/admin/product/image/delete/{id}', name: 'app_admin_product_image_delete', methods: 'POST')]
    public function deleteProductImage(Image $image, Request $request, EntityManagerInterface $em): Response
    {

        if($this->isCsrfTokenValid('product_image_deletion_'. $image->getId(), $request->request->get('csrf_token'))){
            // on recupere le nom du fichier
            $name = $image->getName();
            // on supprime le fichier
            unlink($this->getParameter('images_upload_directory'). '/' . $name);

            $em->remove($image);
            $em->flush();

            $this->addFlash('success', "L'image a été suprimée avec succès");
        }
        
        $id = $image->getProduct()->getId();
        return $this->redirectToRoute('app_admin_product_edit', ['id' => $id], Response::HTTP_SEE_OTHER);
    }
}
