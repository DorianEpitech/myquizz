<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Quizz;
use App\Entity\ResultatQuizz;
use App\Repository\CategorieRepository;
use App\Repository\ReponseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_index', methods: ['GET', 'POST'])]
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('app/index.html.twig', [
            'categories' => $categorieRepository->findAll()
        ]);
    }

    #[Route('/quizz/resultats', name: 'app_result')]
    public function resultats(SessionInterface $session, UserInterface $user = null, EntityManagerInterface $entityManager, Request $request)
    {

        $score = $session->get('score', 0);
        $id = $request->request->get('quizzId');

        $quizz = $entityManager->getRepository(Quizz::class)->find($id);

        if ($user !== null) {
            
            
            $resultatQuizz = new ResultatQuizz();
            
            $resultatQuizz->setIdQuizz($quizz);
            $resultatQuizz->setIdUser($user);
            $resultatQuizz->setResult($score);
            
            $entityManager->persist($resultatQuizz);
            $entityManager->flush();

            $session->set('score', 0);

        } else {

            $history = $session->get('history', []);

            $newEntry = [
                'quizzCategorie' => trim($quizz->getIdCategorie()->getName()),
                'quizzName' => $quizz->getName(),
                'score' => $score,
            ];

            $history[] = $newEntry;

            $session->set('history', $history);
        }
    
        return $this->render('app/score.html.twig', [
            'score' => $score,
        ]);
    }

    #[Route('/quizz/{id}', name: 'app_quizz')]
    public function quizz(Categorie $categorie, CategorieRepository $categorieRepository)
    {
        $quizzs = $categorie->getQuizzs();
        return $this->render('app/quizz.html.twig', [
            'quizzs' => $quizzs,
            'categories' => $categorieRepository->findAll()
        ]);
    }

    #[Route('/quizz/{id}/question/{offset}', name: 'app_question')]

    public function question(Quizz $quizz, CategorieRepository $categorieRepository, Request $request = null, ReponseRepository $reponseRepository, SessionInterface $session, int $offset = 0,)
    {
        $selectedResponse = $request->request->get('response');
        $questionId = $request->request->get('questionId');
        $score = $session->get('score', 0);
        
        if ($questionId == null) {
            $questionId = $quizz->getQuestions()[0]->getId();
            $session->set('score', 0);
            $score = 0;
        } else {
            $questionId = $quizz->getQuestions()[$offset - 1]->getId();
        }

        $expected = $reponseRepository->findOneBy([
            'id_question' => $questionId,
            'reponse_expected' => 1,
        ])->getReponse();
        
        $result = null;

        if ($selectedResponse === null) {
            if ($offset > 0) {
                $offset--;
            }
        } else {
            if ($selectedResponse === $expected)  {

                $result = "Bonne réponse !";
                $score++;
            } else {
    
                $result = "Mauvaise réponse ! La réponse attendue était " . $expected;
            }
        }

        $questions = $quizz->getQuestions()[$offset];
        $offset++;

        $session->set('score', $score);

        $gameOver = ($offset > 10);

        return $this->render('app/question.html.twig', [
            'questions' => $questions,
            'categories' => $categorieRepository->findAll(),
            'offset' => $offset,
            'quizz' => $quizz,
            'result' => $result,
            'score' => $score,
            'gameOver' => $gameOver
        ]);
    }
}