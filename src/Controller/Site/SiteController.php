<?php

namespace App\Controller\Site;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\Profile;
use App\Entity\Category;
use App\Form\ProductType;
use App\Form\CheckoutType;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use App\Repository\ProductRepository;
use App\Service\StimulusNotification;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SiteController extends AbstractController
{
    #[Route('/', name: 'app_site_index')]
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository, CommentRepository $commmentRepository): Response
    {
        $categories =  $categoryRepository->findAll();

        $products = $productRepository->findAll();

        shuffle($products);

        $comments = $commmentRepository->findAll();

        $context = [
            'categories' => $categories,
            'products' => $products,
            'comments' => $comments,
        ];

        return $this->render('site/pages/index.html.twig', $context);
    }

    #[Route('/produits/{slug?}', name: 'app_site_produit')]
    public function produit(?Category $category, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        $categories =  $categoryRepository->findAll();
        $products = $productRepository->findAll();

        if($category){
            $products = $category->getProducts();

            //dd($products);
        }

        $context = [
            'categories' => $categories,
            'products' => $products
        ];

        return $this->render('site/pages/produits.html.twig', $context);
    }

    #[Route('/produits/show/{slug}', name: 'app_site_produit_show')]
    public function produitShow(Product $product): Response
    {

        $context = [
            'product' => $product
        ];

        return $this->render('site/pages/produit_show.html.twig', $context);
    }

    #[Route('/contact', name: 'app_site_contact')]
    public function contact(): Response
    {
        return $this->render('site/pages/contact.html.twig');
    }

    #[Route('/panier', name: 'app_site_panier_list')]
    public function panier(SessionInterface $session, ProductRepository $productRepository, Request $request): Response
    {
        $panier = $session->get("panier", []);

        // on prepare les données
        $dataPanier = [];
        $totalXof = 0;
        $totalEur = 0;
        $totalReduction = 0;

        foreach ($panier as $id => $quantity) {
            
            $product = $productRepository->find($id);

            $dataPanier[] = [
                "product" => $product,
                "quantity" => $quantity
            ];

            if ($request->query->get('currency') && $request->query->get('currency') == 'eur') {
                if(!empty($product->getReduction())){
                    $m = (($product->getPriceEur() * $product->getReduction()) / 100);
                    $totalReduction += $m * $quantity;
                }
            } else {
                if(!empty($product->getReduction())){
                    $m = (($product->getPriceXof() * $product->getReduction()) / 100);
                    $totalReduction += $m * $quantity;
                }
            }
            

            $totalXof += $product->getPriceXof() * $quantity;
            $totalEur += $product->getPriceEur() * $quantity;
        }

        return $this->render('site/pages/panier.html.twig', compact('dataPanier', 'totalXof', 'totalEur', 'totalReduction'));
    }

    #[Route('/panier/{id}', name: 'app_site_panier_add', methods: 'POST')]
    public function add(int $id, ProductRepository $productRepository,  SessionInterface $session, Request $request): Response
    {
        $product = $productRepository->find($id);

        if($this->isCsrfTokenValid('add_to_cart_'. $product->getId(), $request->request->get('csrf_token'))){

            // on recupère le palier
            $panier = $session->get("panier", []);

            if(!empty($panier[$id])){
                $panier[$id]++;
            }else{
                $panier[$id] = 1;
            }    

            $session->set('panier', $panier);
        }

        if($request->query->get('currency') && $request->query->get('currency') == 'eur'){
            return $this->redirectToRoute('app_site_panier_list', ['currency' => 'eur']);
        }
        
        return $this->redirectToRoute('app_site_panier_list');
    }

    #[Route('/panier/remove/{id}', name: 'app_site_panier_remove', methods: 'POST')]
    public function remove(int $id, ProductRepository $productRepository,  SessionInterface $session, Request $request): Response
    {

        $product = $productRepository->find($id);

        if($this->isCsrfTokenValid('remove_to_cart_'. $product->getId(), $request->request->get('csrf_token'))){

            // on recupère le palier
            $panier = $session->get("panier", []);

            // on recupère le palier
            if(!empty($panier[$id])){
                if($panier[$id] > 1){
                    $panier[$id]--;
                }else{
                    unset($panier[$id]);
                }
            }

            $session->set('panier', $panier);
        }

        if($request->query->get('currency') && $request->query->get('currency') == 'eur'){
            return $this->redirectToRoute('app_site_panier_list', ['currency' => 'eur']);
        }
        
        return $this->redirectToRoute('app_site_panier_list');
    }

    #[Route('/panier/delete/{id}', name: 'app_site_panier_delete')]
    public function delete(int $id, ProductRepository $productRepository,  SessionInterface $session, Request $request): Response
    {

        $product = $productRepository->find($id);

        if($this->isCsrfTokenValid('delete_to_cart_'. $product->getId(), $request->request->get('csrf_token'))){

            // on recupère le palier
            $panier = $session->get("panier", []);

            if(!empty($panier[$id])){
                unset($panier[$id]);
            }

            $session->set('panier', $panier);
        }

        if($request->query->get('currency') && $request->query->get('currency') == 'eur'){
            return $this->redirectToRoute('app_site_panier_list', ['currency' => 'eur']);
        }
        
        return $this->redirectToRoute('app_site_panier_list');
    }

    #[Route('/panier/process/', name: 'app_site_panier_checkout')]
    public function checkout(SessionInterface $session, Request $request): Response
    {

        if($this->isCsrfTokenValid('checkout_'. '', $request->request->get('csrf_token'))){

            // on recupère le palier
            $panier = $session->get("panier", []);

            if(!empty($panier)){

                if($request->query->get('currency') && $request->query->get('currency') == 'eur'){
                    return $this->redirectToRoute('app_site_panier_checkout_process', ['currency' => 'eur']);
                }

                return $this->redirectToRoute('app_site_panier_checkout_process');

            }else{

                if($request->query->get('currency') && $request->query->get('currency') == 'eur'){
                    return $this->redirectToRoute('app_site_panier_checkout', ['currency' => 'eur']);
                }

                return $this->redirectToRoute('app_site_panier_checkout');
            }
        }

        if($request->query->get('currency') && $request->query->get('currency') == 'eur'){
            return $this->redirectToRoute('app_site_produit', ['currency' => 'eur']);
        }

        return $this->redirectToRoute('app_site_produit');
        
    }

    #[Route('/panier/process/confirme', name: 'app_site_panier_checkout_process', methods: ['GET', 'POST'])]
    public function checkoutProcess(Request $request, SessionInterface $session, ProductRepository $productRepository, 
        EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, 
        UserRepository $userRepository, StimulusNotification $stimulusNotification
    ): Response
    {
        // on recupère le palier
        $panier = $session->get("panier", []);
        $form = $this->createForm(CheckoutType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $user = $this->getUser();

            $checkout = $form->getData();

            // verifier s'il l'utilsateur est connecté

            $valid = true;
                    
            if(!$user && $request->request->get('create_acount') == true){

                if(!empty($request->request->get('acount_password'))){
                    // on crée le compte du client
                    $user = new User();
                    $user->setEmail($checkout->getEmail());
                    $user->setPassword($passwordHasher->hashPassword($user, $request->request->get('acount_password')));
                    $em->persist($user);
                    $em->flush();

                    // on recupère le user
                    $user = $userRepository->findOneBy(['email' => $checkout->getEmail()]);
                            
                    // on crée le profile
                    $profile = new Profile();
                    $profile->setEmail($checkout->getEmail());
                    $profile->setFirstName($checkout->getFirstName());
                    $profile->setLastName($checkout->getLastName());
                    $profile->setCodePostal($checkout->getCodePostal());
                    $profile->setContact($checkout->getContact());
                    $profile->setPays($checkout->getPays());
                    $profile->setVille($checkout->getVille());
                    $profile->setCommune($checkout->getCommune());
                    $profile->setUser($user);

                    $em->persist($profile);
                    $em->flush();

                }else{
                    $valid = false;
                }

                

            }

            if($valid){
                // on enregistre la commande

                $serialize_panier = serialize($panier); // $newvar = unserialize($string);
                
                $checkout->setCheckout($serialize_panier);

                if($user){
                    $checkout->setCreatedBy($user->getProfile());
                }

                $em->persist($checkout);
                $em->flush();

                // envoi de la notification
                $stimulusNotification->sendCommandeNotification(['confirmed' => true]);
                // on  suprime le panier
                $session->set('panier', []);

                $this->addFlash('success', "Votre panier à bien été validé, nous vous remerçions pour votre confiance.");

                if($request->query->get('currency') && $request->query->get('currency') == 'eur'){
                    return $this->redirectToRoute('app_site_produit', ['currency' => 'eur'], Response::HTTP_SEE_OTHER);
                }

                return $this->redirectToRoute('app_site_produit', [], Response::HTTP_SEE_OTHER);

            }else{
                $this->addFlash('warning', "Si vous souhaitez créer automatiquement un compte, veuillez renseigner le mot de passe.");
            }
            
        }

        if($form->isSubmitted() && !$form->isValid()){
            $this->addFlash('warning', "Votre panier n'à été correctement validé");
        }

        $dataPanier = [];
        $totalXof = 0;
        $totalReduction = 0;
        $totalEur = 0;

        foreach ($panier as $id => $quantity) {
            
            $product = $productRepository->find($id);

            $dataPanier[] = [
                "product" => $product,
                "quantity" => $quantity
            ];

            if ($request->query->get('currency') && $request->query->get('currency') == 'eur') {
                if(!empty($product->getReduction())){
                    $m = (($product->getPriceEur() * $product->getReduction()) / 100);
                    $totalReduction += $m * $quantity;
                }
            } else {
                if(!empty($product->getReduction())){
                    $m = (($product->getPriceXof() * $product->getReduction()) / 100);
                    $totalReduction += $m * $quantity;
                }
            }
            

            $totalXof += $product->getPriceXof() * $quantity;
            $totalEur += $product->getPriceEur() * $quantity;
        }

        $context = [
            'dataPanier' => $dataPanier,
            'totalXof' => $totalXof,
            'totalEur' => $totalEur,
            'totalReduction' => $totalReduction,
            'form' => $form,
            'currency' => 'eur'
        ];
        
        return $this->renderForm('site/pages/checkout.html.twig', $context);

    }
}
