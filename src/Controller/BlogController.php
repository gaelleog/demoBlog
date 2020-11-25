<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
     */
    public function create(Request $request, EntityManagerInterface $manager)
    {
        dump($request);

        if($request->request->count() > 0)
        {
            $article = new Article;
            $article->setTitle($request->request->get('title'))
                    ->setContent($request->request->get('content'))
                    ->setImage($request->request->get('image'))
                    ->setCreatedAt(new \DateTime());
            
            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('blog/create.html.twig');
    }

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article) : Response
    {
        // $repo = $this->getDoctrine()->getRepository(Article::class);

        // $article = $repo->find($id);
        // dump($article);

        return $this->render('blog/show.html.twig', [
            'article' => $article
        ]);
    }
}

