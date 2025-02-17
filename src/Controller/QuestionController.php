<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class QuestionController extends AbstractController
{
    #[Route('/question/ask', name: 'question_form')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {

        $question = new Question();

        $formQuestion = $this->createForm(QuestionType::class, $question);

        $formQuestion->handleRequest($request);

        if ($formQuestion->isSubmitted() && $formQuestion->isValid()) {
            $question->setNbrOfResponse(0);
            $question->setRating(0);
            $question->setCreatedAt(new \DateTimeImmutable());
            $em->persist($question);
            $em->flush();
            // success correspond à la categorie et puis l'ajout du msg dans la categorie
            $this->addFlash('success', 'Votre question a été ajoutée');
            return $this->redirectToRoute('home');
        }

        return $this->render('question/index.html.twig', [
            'form' => $formQuestion->createView(),
        ]);
    }
    #[Route('/question/{id}', name: 'question_show')]
    // recup de l'id à partir de convertisseur des paramètres
    public function show(Request $request, Question $question, EntityManagerInterface $em): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setRating(0);
            $comment->setQuestion($question);
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', 'Votre reponse a bien été ajoutée ');
            return $this->redirect($request->getUri());
        }

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'form' => $commentForm->createView()
        ]);
    }
}
