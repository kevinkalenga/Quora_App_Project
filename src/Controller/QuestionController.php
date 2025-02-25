<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Entity\Question;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
// use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\VoteRepository;

final class QuestionController extends AbstractController
{
    #[Route('/question/ask', name: 'question_form')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $question = new Question();

        $formQuestion = $this->createForm(QuestionType::class, $question);

        $formQuestion->handleRequest($request);

        if ($formQuestion->isSubmitted() && $formQuestion->isValid()) {
            $question->setNbrOfResponse(0);
            $question->setRating(0);
            $question->setAuthor($user);
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
        // $question = $questionsRepo->getQuestionWithCommentsAndAuthors($id);

        $options = [
            'question' => $question,
        ];
        $user = $this->getUser();
        if ($user) {
            $comment = new Comment();
            $commentForm = $this->createForm(CommentType::class, $comment);
            $commentForm->handleRequest($request);

            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $comment->setCreatedAt(new \DateTimeImmutable());
                $comment->setRating(0);
                $comment->setQuestion($question);
                $comment->setAuthor($user);
                $question->setNbrOfResponse($question->getNbrOfResponse() + 1);
                $em->persist($comment);
                $em->flush();
                $this->addFlash('success', 'Votre reponse a bien été ajoutée');
                return $this->redirect($request->getUri());
            }
            $options['form'] = $commentForm->createView();
        }

        return $this->render('question/show.html.twig', $options);
    }

    #[Route('/question/rating/{id}/{score}', name: 'question_rating')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function ratingQuestion(Request $request, VoteRepository $voteRepo, Question $question, int $score, EntityManagerInterface $em)
    {

        $user = $this->getUser();

        if ($user !== $question->getAuthor()) {

            $vote = $voteRepo->findBy([
                'author' => $user,
                'question' => $question
            ])[0] ?? null;
            if ($vote) {

                if (($vote->isLiked() && $score > 0) || (!$vote->isLiked() && $score < 0)) {
                    $em->remove($vote);
                    $question->setRating($question->getRating() + ($score > 0 ? -1 : 1));
                } else {
                    $vote->setIsLiked(!$vote->isLiked());
                    $question->setRating($question->getRating() + ($score > 0 ? 2 : -2));
                }
            } else {
                $vote = new Vote();
                $vote->setAuthor($user);
                $vote->setQuestion($question);
                $vote->setIsLiked($score > 0 ? true : false);
                $question->setRating($question->getRating() + $score);
                $em->persist($vote);
            }


            $em->flush();
        }


        $referer = $request->server->get('HTTP_REFERER');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('home');
    }

    #[Route('/comment/rating/{id}/{score}', name: 'comment_rating')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function ratingComment(Request $request, VoteRepository $voteRepo, Comment $comment, int $score, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        if ($user !== $comment->getAuthor()) {

            $vote = $voteRepo->findBy([
                'author' => $user,
                'comment' => $comment
            ])[0] ?? null;
            if ($vote) {

                if (($vote->isLiked() && $score > 0) || (!$vote->isLiked() && $score < 0)) {
                    $em->remove($vote);
                    $comment->setRating($comment->getRating() + ($score > 0 ? -1 : 1));
                } else {
                    $vote->setIsLiked(!$vote->isLiked());
                    $comment->setRating($comment->getRating() + ($score > 0 ? 2 : -2));
                }
            } else {
                $vote = new Vote();
                $vote->setAuthor($user);
                $vote->setComment($comment);
                $vote->setIsLiked($score > 0 ? true : false);
                $comment->setRating($comment->getRating() + $score);
                $em->persist($vote);
            }


            $em->flush();
        }
        $referer = $request->server->get('HTTP_REFERER');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('home');
    }
}
