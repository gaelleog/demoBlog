<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
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
    public function adminForm(Request $request, EntityManagerInterface $manager, Article $article = null): Response
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
}
