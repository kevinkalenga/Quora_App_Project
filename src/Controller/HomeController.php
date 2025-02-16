<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {


        $questions = [
            [
                'id' => '1',
                'title' => "Je suis une super question",
                'content' => "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression. Le Lorem Ipsum est le faux texte standard de l'imprimerie depuis les années 1500, quand un imprimeur anonyme assembla ensemble des morceaux de texte pour réaliser un livre spécimen de polices de texte",
                'rating' => 20,
                'author' => [
                    'name' => "Xiping Golba",
                    'avatar' => "https://randomuser.me/api/portraits/men/90.jpg"
                ],
                'nbrOfResponse' => 15
            ],
            [
                'id' => '2',
                'title' => "Je suis une super question",
                'content' => "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression. Le Lorem Ipsum est le faux texte standard de l'imprimerie depuis les années 1500, quand un imprimeur anonyme assembla ensemble des morceaux de texte pour réaliser un livre spécimen de polices de texte",
                'rating' => 0,
                'author' => [
                    'name' => "Camara Diallo",
                    'avatar' => "https://randomuser.me/api/portraits/men/83.jpg"
                ],
                'nbrOfResponse' => 15
            ],
            [
                'id' => '3',
                'title' => "Je suis une super question",
                'content' => "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression. Le Lorem Ipsum est le faux texte standard de l'imprimerie depuis les années 1500, quand un imprimeur anonyme assembla ensemble des morceaux de texte pour réaliser un livre spécimen de polices de texte",
                'rating' => -15,
                'author' => [
                    'name' => "Jean Dupont",
                    'avatar' => "https://randomuser.me/api/portraits/men/46.jpg"
                ],
                'nbrOfResponse' => 15
            ],
        ];




        return $this->render('home/index.html.twig', [
            'questions' => $questions
        ]);
    }
}
