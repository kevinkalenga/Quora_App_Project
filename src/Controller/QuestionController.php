<?php

namespace App\Controller;

use App\Form\QuestionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class QuestionController extends AbstractController
{
    #[Route('/question/ask', name: 'question_form')]
    public function index(Request $request): Response
    {
        $formQuestion = $this->createForm(QuestionType::class);

        $formQuestion->handleRequest($request);

        if ($formQuestion->isSubmitted() && $formQuestion->isValid()) {
        }

        return $this->render('question/index.html.twig', [
            'form' => $formQuestion->createView(),
        ]);
    }
    #[Route('/question/{id}', name: 'question_show')]
    public function show(Request $request, string $id): Response
    {

        $question = [
            'title' => "Je suis une super question",
            'content' => "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression. Le Lorem Ipsum est le faux texte standard de l'imprimerie depuis les années 1500, quand un imprimeur anonyme assembla ensemble des morceaux de texte pour réaliser un livre spécimen de polices de texte",
            'rating' => 20,
            'author' => [
                'name' => "Xiping Golba",
                'avatar' => "https://randomuser.me/api/portraits/men/90.jpg"
            ],
            'nbrOfResponse' => 15
        ];

        return $this->render('question/show.html.twig', [
            'question' => $question,
        ]);
    }
}
