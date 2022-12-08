<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    #[Route('/admin/comment', name: 'app_admin_comment_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, CommentRepository $repos): Response
    {

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $comment = $form->getData();

            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', "Le commentaire a été ajouté avec succès");

            return $this->redirectToRoute('app_admin_comment_index', [], Response::HTTP_SEE_OTHER);
            
        }

        if($form->isSubmitted() && !$form->isValid()){
            $this->addFlash('warning', "Le commentaire n'a pas été ajouté, veuillez réessayer.");
        }

        $comments = $repos->findAll();
        $context = [
            'comment_form' => $form,
            'comments' => $comments,
        ];

        return $this->renderForm('admin/comment/index.html.twig', $context);
    }

    #[Route('/admin/comment/edit/{id}', name: 'app_admin_comment_edit', methods: ['GET', 'POST'])]
    public function edit(comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(commentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $comment = $form->getData();

            $em->flush();

            $this->addFlash('success', "Le commentaire a été modifié avec succès");

            return $this->redirectToRoute('app_admin_comment_index', [], Response::HTTP_SEE_OTHER);
        }

        if($form->isSubmitted() && !$form->isValid()){
            $this->addFlash('warning', "Le commentaire n'a pas été modifié, veuillez réessayer.");
        }
        
        $context = [
            'comment_form' => $form,
        ];

        return $this->renderForm('admin/comment/edit.html.twig', $context);
    }

    #[Route('/admin/comment/delete/{id}', name: 'app_admin_comment_delete', methods: 'POST')]
    public function delete(comment $comment, Request $request, EntityManagerInterface $em): Response
    {

        if($this->isCsrfTokenValid('comment_deletion_'. $comment->getId(), $request->request->get('csrf_token'))){
            $em->remove($comment);
            $em->flush();

            $this->addFlash('success', "Le commentaire a été suprimé avec succès");
        }
        

        return $this->redirectToRoute('app_admin_comment_index');
    }
}
