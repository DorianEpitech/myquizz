<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Entity\Question;
use App\Entity\User;
use App\Entity\Categorie;
use App\Entity\Quizz;
use App\Form\SendEmail;
use App\Form\UserType;
use App\Form\CategorieType;
use App\Form\QuizzType;
use App\Repository\CategorieRepository;
use App\Repository\QuizzRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted("ROLE_ADMIN")]
class AdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/categories', name: 'app_display_categories', methods: ['GET', 'POST'])]
    public function getCategories(QuizzRepository $quizzRepository): Response
    {
        $categoriesWithCount = $quizzRepository->getQuizzStats();

        return $this->render('admin/categories/index.html.twig', [
            'resultatQuizz' => $categoriesWithCount,
        ]);
    }

    #[Route('/categories/new', name: 'app_new_categorie', methods: ['GET', 'POST'])]

    public function newCategorie(Request $request, CategorieRepository $categorieRepository): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categorieRepository->save($categorie, true);

            return $this->redirectToRoute('app_display_categories', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/categories/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/categories/{id}/edit', name: 'app_categorie_edit', methods: ['GET', 'POST'])]
    public function editCategorie(Request $request, Categorie $categorie, CategorieRepository $categorieRepository): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categorieRepository->save($categorie, true);

            return $this->redirectToRoute('app_display_categories', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/categories/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/categories/{id}', name: 'app_stats_categorie', methods: ['GET'])]
    public function getCategorieStats(Categorie $categorie): Response
    {
        $quizzs = $categorie->getQuizzs();
        $quizzsCount = $quizzs->count();

        $totalNote = 0;
        $resultsCount = 0;

        foreach ($quizzs as $quizz) {

            $resultatsQuizz = $quizz->getResultatQuizzs();
            $resultsCount += $resultatsQuizz->count();

            foreach($resultatsQuizz as $result ) {
                 $totalNote += $result->getResult();
            }
        }

        $averageNote = $quizzsCount > 0 && $resultsCount > 0 ? $totalNote / $resultsCount : 0;

        return $this->render('admin/categories/stats.html.twig', [
            'quizzs' => $quizzs,
            'categorie' => $categorie,
            'quizzsCount' => $quizzsCount,
            'resultCount' => $resultsCount,
            'average' => $averageNote,
        ]);
    }

    #[Route('/categories/{id}', name: 'app_categorie_delete', methods: ['POST'])]
    public function deleteCategorie(Request $request, Categorie $categorie, CategorieRepository $categorieRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->request->get('_token'))) {
            $categorieRepository->remove($categorie, true);
        }

        return $this->redirectToRoute('app_display_categories', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/categories/quizz/new', name: 'app_new_quizz', methods: ['GET', 'POST'])]
    public function newQuizz(Request $request, QuizzRepository $quizzRepository, CategorieRepository $categorieRepository): Response
    {
        $quizz = new Quizz();

        $choices = [];
        $categorieRepository = $categorieRepository->findAll();

        foreach ($categorieRepository as $categorie) {
            $choices[] = [$categorie->getName() => $categorie];
        }

        $form = $this->createForm(QuizzType::class, null, ["choices" => $choices]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $quizz->setIdCategorie($data['id_categorie']);
            $quizz->setName($data['name']);

            // je persist afin de pouvoir récupérer l'id du quizz pour les questions qui arrivent
            $this->entityManager->persist($quizz);
            $this->entityManager->flush();

            // je boucle sur mes 10 questions
            for ($i = 1; $i <= 10; $i++) {
                $question = new Question(); // je reinstancie bien question a chaque itération pour avoir une entité avec un nouvel ID
                $question->setIdQuizz($quizz); // notre entité quizz a le bon id grace au persist fait plus haut
                $question->setQuestion($data['question_'.$i]); // je stock la question numéro $i dans l'entité

                // je persist avant chaque réponse pour que l'id de question soit detectable pour réponse
                $this->entityManager->persist($question);
                $this->entityManager->flush(); // envoi à la db

                $reponseExpectedField = 'reponse_expected_'.$i; // je récupère la bonne réponse (celle cochée)
                $reponseExpected = $data[$reponseExpectedField];

                // je boucle sur les 3 réponses de ma question
                for ($j = 1; $j <= 3; $j++) {
                    $reponse = new Reponse();
                    $reponse->setIdQuestion($question);
                    $reponse->setReponse($data['reponse_'.$i.'_'.$j]); // je stock la réponse $j de la question $i

                    if ($reponseExpected === 'reponse_'.$i.'_'.$j) { // si la reponse expected cochée est celle ci, je mets la valeur a true
                        $reponse->setReponseExpected(true);
                    } else {
                        $reponse->setReponseExpected(false);
                    }

                    $this->entityManager->persist($reponse);
                    $this->entityManager->flush();
                }
            }

            return $this->redirectToRoute('app_display_categories', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/quizzs/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categories/quizz/{id}/edit', name: 'app_edit_quizz', methods: ['GET', 'POST'])]
    public function editQuizz(Request $request, Quizz $quizz, QuizzRepository $quizzRepository, CategorieRepository $categorieRepository): Response
    {
        $choices = [];
        $categorieRepository = $categorieRepository->findAll();

        $questions = $quizz->getQuestions();
        $questionsData = [];

        foreach ($questions as $index => $question) {
            $questionData = [
                'question_' . ($index + 1) => $question->getQuestion(),
            ];

            $reponses = $question->getReponses();
            $reponsesData = [];

            foreach ($reponses as $reponseIndex => $reponse) {
                $reponsesData['reponse_' . ($index + 1) . '_' . ($reponseIndex + 1)] = $reponse->getReponse();

                if ($reponse->getReponseExpected()) {
                    $questionData['reponse_expected_' . ($index + 1)] = 'reponse_' . ($index + 1) . '_' . ($reponseIndex + 1);
                }
            }

            $questionsData = array_merge($questionsData, $questionData, $reponsesData);
        }

        $formData = array_merge([
            'id_categorie' => $quizz->getIdCategorie(),
            'name' => $quizz->getName(),
        ], $questionsData);

        foreach ($categorieRepository as $categorie) {
            $choices[] = [$categorie->getName() => $categorie];
        }

        $form = $this->createForm(QuizzType::class, $formData, ["choices" => $choices]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $quizz->setIdCategorie($data['id_categorie']);
            $quizz->setName($data['name']);

            $this->entityManager->flush();

            $questions = $quizz->getQuestions();
            foreach ($questions as $question) {
                $this->entityManager->remove($question);
            }
            $this->entityManager->flush();

            for ($i = 1; $i <= 10; $i++) {
                $question = new Question();
                $question->setIdQuizz($quizz);
                $question->setQuestion($data['question_'.$i]);

                $this->entityManager->persist($question);
                $this->entityManager->flush();

                $reponseExpectedField = 'reponse_expected_'.$i;
                $reponseExpected = $data[$reponseExpectedField];

                for ($j = 1; $j <= 3; $j++) {
                    $reponse = new Reponse();
                    $reponse->setIdQuestion($question);
                    $reponse->setReponse($data['reponse_'.$i.'_'.$j]);

                    if ($reponseExpected === 'reponse_'.$i.'_'.$j) {
                        $reponse->setReponseExpected(true);
                    } else {
                        $reponse->setReponseExpected(false);
                    }

                    $this->entityManager->persist($reponse);
                    $this->entityManager->flush();
                }
            }

            return $this->redirectToRoute('app_display_categories', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/quizzs/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/categories/quizz/{id}', name: 'app_delete_quizz', methods: ['POST'])]

    public function deleteQuizz(Request $request, Quizz $quizz, QuizzRepository $quizzRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quizz->getId(), $request->request->get('_token'))) {
            $quizzRepository->remove($quizz, true);
        }

        return $this->redirectToRoute('app_display_categories', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/categories/quizz/{id}', name: 'app_stats_quizz', methods: ['GET'])]
    public function getQuizzStats(Quizz $quizz): Response
    {
        $resultatQuizzs = $quizz->getResultatQuizzs();
        $quizzCount = $resultatQuizzs->count();

        $totalNote = 0;

        foreach ($resultatQuizzs as $resultatQuizz) {
            $totalNote += $resultatQuizz->getResult();
        }

        $averageNote = $quizzCount > 0 ? $totalNote / $quizzCount : 0;

        return $this->render('admin/quizzs/stats.html.twig', [
            'quizzCount' => $quizzCount,
            'average' => $averageNote,
            "quizz" => $quizz
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/users/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->remove('password');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/send', name: 'app_user_send', methods: ['GET', 'POST'])]
    public function sendEmail(Request $request, User $user, UserRepository $userRepository, MailerInterface $mailer): Response
    {
        $form = $this->createForm(SendEmail::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $emailChoice = $data['emailChoice'];

            switch ($emailChoice) {
                case 'inactive':
                    $emailSubject = '1 mois sans vous';
                    $emailContent = '<p>Cela fait 1 mois que nous ne vous avons pas vu, revenez !</p>';
                    break;
                case 'congrats':
                    $emailSubject = 'Félicitations !';
                    $emailContent = '<p>Bravo pour vos multiples victoires sur nos Quizz !</p>';
                    break;
                default:
                    return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }

            $email = (new Email())
                ->from('your_email@example.com')
                ->to($user->getEmail())
                ->subject($emailSubject)
                ->html($emailContent);

            $mailer->send($email);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/users/send.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/stats/{id}', name: 'app_stats_user', methods: ['GET', 'POST'])]
    public function getUserStats(User $user): Response
    {
        $resultatQuizzs = $user->getResultatQuizzs();
        $quizzCount = $resultatQuizzs->count();
        $userEmail = $user->getEmail();

        $totalNote = 0;

        foreach ($resultatQuizzs as $resultatQuizz) {
            $totalNote += $resultatQuizz->getResult();
        }

        $averageNote = $quizzCount > 0 ? $totalNote / $quizzCount : 0;

        return $this->render('admin/users/stats.html.twig', [
            'quizzCount' => $quizzCount,
            'averageNote' => $averageNote,
            'email' => $userEmail
        ]);
    }
}