<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    // Chaque méthode du controller est associée à une route bien spécifique
    // Lorsque nous envoyons la route '/blog' dans l'URL du navigateur, cela exécute automatiquement dans le controller la méthode associée à celle-ci
    // Chaque méthode renvoit un template sur le navigateur en fonction de la route transmise

    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo): Response
    {
        // $repo = $this->getDoctrine()->getRepository(Article::class);
        // dump($repo);

        $article = $repo->findAll();
        dump($article);

        return $this->render('blog/index.html.twig', [
            'articles' => $article,
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        return $this->render('blog/home.html.twig', [
            'title' => 'Bienvenue sur le blog Symfony',
            'age' => 25
        ]);
    }
    /**
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager)
    {
        // Nous avons défini 2 routes différentes, une pour l'insertion et une pour la modification
        // Lorsque l'on envoie la route '/blog/new' dans l'URL, on définit un Article $article NULL, sinon Symfony tente de récupérer un article en BDD et nous avons une erreur
        // Lorsque l'on envoie la route '/blog/{id}/edit', Symfony selectionne en BDD l'article en fonction de l'id transmit dans l'URL et écrase NULL par l'article recupéré en BDD dans l'objet $article

        // La classe Request contient toutes les données véhiculées par les superglobales ($_POST, $_GET, $_FILES...)

        // Si des données ont bien été saisies dans le formulaire
        // if($request->request->count() > 0)
        // {
        //     $article = new Article;
        //     $article->setTitle($request->request->get('title'))
        //             ->setContent($request->request->get('content'))
        //             ->setImage($request->request->get('image'))
        //             ->setCreatedAt(new \DateTime());

        //     $manager->persist($article);
        //     $manager->flush();

        //     return $this->redirectToRoute('blog_show', [
        //         'id' => $article->getId()
        //     ]);
        // }

        // On entre dans la condition IF seulement dans le cas de la création d'un nouvel article, càd pour la route '/blog/new', $article est NULL, on crée un nouvel objet Article
        // Dans le cas d'une modification, $article n'est pas NULL, il contient l'article selectionné en BDD à modifier, on n'entre pas dans la condition IF
        if (!$article) {
            $article = new Article;
        }

        // On observe qu'en remplissant l'objet Article via les setteurs, les getteurs renvoient les données de l'article directement dans les champs du formulaire
        // $article->setTitle("Titre du truc")
        //         ->setContent("Contenu du truc");

        // createFormBuilder() : methode issue de la classe BlogController permettant de créer un formulaire HTML qui sera lié à notre objet Article, c'est à dire que les champs du formulaire vont remplir l'objet Article

        // $form = $this->createFormBuilder($article)
        //             ->add('title') // Permet de créer des champs du formulaire
        //             ->add('content')
        //             ->add('image')
        //             ->getForm(); // Permet de valider le formulaire


        // On importe la classe ArticleType qui permet de générer le formulaire d'ajout / modification des articles
        // On précise que le formulaire a pour but de remplir les setteurs de l'objet $article
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request); // handleRequest permet de vérifier si tous les champs ont bien été remplis et la méthode va bindé l'objet Article, c'est à dire que si un titre de l'article a été saisi, il sera envoyé directement dans le bon setter de l'objet Article

        dump($request); // On observe les données saisies dans le formulaire dans la propriété 'request'

        // Si le formulaire a bien été soumis et que toutes les données sont valides, alors on entre dans la condition IF
        if ($form->isSubmitted() && $form->isValid()) {
            // Si l'article n'a pas d'ID, cela veut dire que nous sommes dans le cas d'une insertion, alors on entre dans la condition IF
            if (!$article->getId()) {
                $article->setCreatedAt(new \DateTime()); // on rempli le setter de la date puisque nous n'avons pas de champs date dans le formulaire
            }

            $manager->persist($article); // On prépare l'insertion en BDD
            $manager->flush(); // On exécture l'insertion en BDD

            // Une fois l'insertion exécutée, on redirige vers le détail de l'article qui vient d'être inséré
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId() // On transmet dans la route, l'ID de l'article qui vient d'être inséré grâce au getter de l'objet Article
            ]);
        }

        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null // si l'id de l'article est différent de NULL, alors 'ediMode' renvoie TRUE et que c'est une modification
        ]);
    }

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article, Request $request, EntityManagerInterface $manager): Response
    {
        // $repo = $this->getDoctrine()->getRepository(Article::class);

        // $article = $repo->find($id);
        // dump($article);

        $comment = new Comment;

        dump($request);

        $formComment = $this->createForm(CommentType::class, $comment);

        $formComment->handleRequest($request);

        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $comment->setCreatedAt(new \DateTime); // on insère une date de création du commentaire
            $comment->setArticle($article); // on relie le commentaire à l'article (clé étrangère)

            $manager->persist($comment); // on prépare l'insertion
            $manager->flush(); // on exécute l'insertion

            // Envoi d'un message de validation
            $this->addFlash('success', "Le commentaire a été posté!");

            // on redirige vers l'article après l'insertion du commentaire
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }


        return $this->render('blog/show.html.twig', [
            'article' => $article, // on envoie sur le template l'article sélectionné en BDD
            'formComment' => $formComment->createView()
        ]);
    }
}
