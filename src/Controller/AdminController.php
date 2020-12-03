<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/articles", name="admin_articles")
     */
    public function adminArticles(EntityManagerInterface $manager, ArticleRepository $repo): Response
    {
        // via le manager qui permet de manipuler la BDD (insert, update, delete etc..), on exécute la méthode getClassMetadata() afin de sélectionner les méta données (primary key, not null, nom des champs etc...) d'une entité (donc d'une table SQL), afin de sélectionner le nom des champs/ colonnes de la table grâce à la méthode getFieldNames().
        $colonnes = $manager->getClassMetadata(Article::class)->getFieldNames();

        dump($colonnes);

        // On sélectionne l'ensemble des articles de la table SQL 'article' dans la BDD en passant par la classe ArticleRepository qui permet de sélectionner dans la table SQL 'article' et la méthode 'findAll() qui permet de sélectionner l'ensemble de la table (SELECT * FROM article + FETCHALL)
        $articles = $repo->findAll();

        dump($articles);

        return $this->render('admin/admin_articles.html.twig', [
            'colonnes' => $colonnes,
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/admin/article/new", name="admin_new_article")
     * @Route("/admin/{id}/edit-article", name="admin_edit_article")
     */
    public function adminFormArticle(Request $request, EntityManagerInterface $manager, Article $article = null): Response
    {
        if (!$article) {
            $article = new Article;
        }

        $formArticle = $this->createForm(ArticleType::class, $article);

        dump($request);

        $formArticle->handleRequest($request);

        if ($formArticle->isSubmitted() && $formArticle->isValid()) {
            if (!$article->getId()) {
                $article->setCreatedAt(new \DateTime());
                $this->addFlash('success', "L'article a bien été enregistré");
            } else {
                $this->addFlash('success', "L'article a bien été modifié");
            }

            $manager->persist($article);
            $manager->flush();


            return $this->redirectToRoute('admin_articles');
        }

        return $this->render('admin/admin_create.html.twig', [
            'formArticle' => $formArticle->createView()
        ]);
    }
    /**
     * @Route("admin/{id}/delete-article", name="admin_delete_article")
     */
    public function deleteArticle(Article $article, EntityManagerInterface $manager)
    {
        // Nous avons définit une route paramétrée (id) afin de pouvoir supprimer cet article dans la BDD
        // Nous avons injecté en dépendance l'entité article afin que Symfony sélectionne automatiquement en BDD l'article à supprimer
        // remove() : méthode de l'interface EntityManagerInterface qui permet de préparer et garder en mémoire la requête DELETE de suppression
        $manager->remove($article);
        $manager->flush(); // exécute la requête de suppression en BDD

        // On affiche un message de validation de suppression
        $this->addFlash('success', "L'article a bien été supprimé");

        // On redirige vers l'affichage des articles dans le backoffice après la suppression
        return $this->redirectToRoute('admin_articles');
    }

    /**
     * @Route("admin/category", name="admin_category")
     */
    public function adminCategory(EntityManagerInterface $manager, CategoryRepository $repo): Response
    {
        $colonnes = $manager->getClassMetadata(Category::class)->getFieldNames();

        dump($colonnes);

        $categories = $repo->findAll();

        dump($categories);

        return $this->render('admin/admin_category.html.twig', [
            'colonnes' => $colonnes,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/admin/category/new", name="admin_new_category")
     * @Route("/admin/{id}/edit-category", name="admin_edit_category")
     */
    public function adminFormCategory(Request $request, EntityManagerInterface $manager, Category $category = null): Response
    {
        // L'entité Category représente un modèle de la table SQL Category, donc pour pouvoir insérer dans la table Category, nous devons renseigner les setter de l'objet avec les données du formulaire

        if (!$category) {
            $category = new Category;
        }

        dump($category);

        $formCategory = $this->createForm(CategoryType::class, $category); // on crée le formulaire d'ajout/modif des catégories et on relit le formulaire à l'entité $category

        dump($request);

        $formCategory->handleRequest($request); // on récupère les données du formulaire afin de les renvoyer dans les setters

        if ($formCategory->isSubmitted() && $formCategory->isValid()) {
            if (!$category->getId())
                $message = "La catégorie a bien été enregistrée";
            else
                $message = "La catégorie a bien été modifiée";

            $this->addFlash('success', $message);


            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('admin_category');
        }

        return $this->render('admin/admin_create_category.html.twig', [
            'formCategory' => $formCategory->createView(),
            'editMode' => $category->getId()
        ]);
    }

    /**
     * @Route("admin/{id}/delete-category", name="admin_delete_category")
     */
    public function adminDeleteCategory(Category $category, EntityManagerInterface $manager)
    {
        if ($category->getArticles()->isEmpty()) {
            $manager->remove($category);
            $manager->flush();

            $this->addFlash('success', "La catégorie a bien été supprimée");
        } else {
            $this->addFlash('danger', "Il n'est pas possible de supprimer la catégorie car des articles y sont toujours associés!");
        }
        return $this->redirectToRoute('admin_category');
    }
}
