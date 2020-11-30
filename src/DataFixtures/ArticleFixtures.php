<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /*
            Les fixtures permettent de créer des données fictives, des fausses données en BDD

            Nous créons ici une boucle afin de créer 10 articles en BDD
            Pour pouvoir insérer des articles en BDD, nous devons passer par l'entité Articles qui reflète la table SQL
        */

        // for($i = 1; $i <= 10; $i++)
        // {
        //     // Pour chaque tour de boucle, on créer un objet Article vide
        //     $article = new Article;

        //     // On renseigne tout les setters de l'entité Article
        //     $article->setTitle("Titre de l'article n°$i")
        //             ->setContent("<p>Contenu de l'article n°$i</p>")
        //             ->setImage("https://picsum.photos/200/300")
        //             ->setCreatedAt(new \DateTime());

        //     // ObjectManager permet de manipuler les lignes dans la BDD (INSERT,UPDATE,DELETE)
        //     // persist() : permet de préaprer les requetes d'insertions
        //     $manager->persist($article); // prepare la requette d'insertion
        // }

        // // flush() : permet de libérer l'insertion en BDD
        // $manager->flush(); // Execute l'insertion en BDD

        $faker = \Faker\Factory::create('fr_FR');

        // Création de 3 catégories
        for ($i = 1; $i <= 3; $i++) {
            $category = new Category;

            $category->setTitle($faker->sentence())
                ->setDescription($faker->paragraph());

            $manager->persist($category);

            // Création de 4 à 6 articles
            for ($j = 1; $j <= mt_rand(4, 6); $j++) {
                $article = new Article;

                $content = '<p>' . join($faker->paragraphs(5), '<p></p>') . '</p>';

                $article->setTitle($faker->sentence())
                    ->setContent($content)
                    ->setImage($faker->imageUrl())
                    ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                    ->setCategory($category);

                $manager->persist($article);

                // Création de 4 à 10 commentaires
                for ($k = 1; $k <= mt_rand(4, 10); $k++) {
                    $comment = new Comment;

                    $content = '<p>' . join($faker->paragraphs(2), '<p></p>') . '</p>';

                    $now = new \DateTime();
                    $interval = $now->diff($article->getCreatedAt()); // retourne le temps (timestamp) entre la date de création des articles et aujourd'hui
                    $days = $interval->days; // nombre de jour en tre la date de création des articles et maintenant
                    $minimum = '-' . $days . 'days'; // -100 days le but est d'avoir des dates de commentaires, entre par exemple -100 days et aujourd'hui

                    $comment->setAuthor($faker->name)
                        ->setContent($content)
                        ->setCreatedAt($faker->dateTimeBetween($minimum))
                        ->setArticle($article);

                    $manager->persist($comment);
                }
            }
        }

        $manager->flush();
    }
}
