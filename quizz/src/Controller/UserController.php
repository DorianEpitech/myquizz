<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quizz;
use App\Entity\Reponse;
use App\Entity\ResultatQuizz;
use App\Form\ChangeEmailType;
use App\Form\ChangePasswordType;
use App\Form\QuizzType;
use App\Repository\CategorieRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{

    private EmailVerifier $emailVerifier;

    private $entityManager;

    public function __construct(EmailVerifier $emailVerifier, EntityManagerInterface $entityManager)
    {
        $this->emailVerifier = $emailVerifier;
        $this->entityManager = $entityManager;
    }

    #[Route('/create', name: 'user_new_quizz', methods: ['GET', 'POST'])]
    public function createQuizz(Request $request, CategorieRepository $categorieRepository): Response
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

            return $this->redirectToRoute('app_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/connected/create_quizz.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile', name: 'app_user_profile')]
    public function index(Security $security): Response
    {
        $user = $security->getUser();
       
        return $this->render('user/connected/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/historique', name: 'app_user_history')]
    public function displayHistory (UserInterface $user = null, EntityManagerInterface $entityManager, SessionInterface $session) {

        $categorie = [];
        $history = [];

        if ($user !== null) {

            $history = $entityManager->getRepository(ResultatQuizz::class)->findBy([
                'id_user' => $user->getId(),
            ]);
    
    
            foreach ($history as $item) {
                $quizz = $entityManager->getRepository(Quizz::class)->find($item->getIdQuizz());
                array_push($categorie, $quizz->getIdCategorie()->getName());
            }
        }

    
        return $this->render('user/connected/history.html.twig', [
            'history' => $history,
            'categorie' => $categorie
        ]);
    }
  
    #[Route('/change-email', name: 'app_change_email')]
    public function changeEmail(Request $request, EntityManagerInterface $entityManager): Response
    {   
        if ($this->getUser()) {

            $user = $this->getUser();

            $form = $this->createForm(ChangeEmailType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $user->setEmail($form->get('newEmail')->getData());
                $user->setIsVerified(false);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('my_quizz@noreply.com', 'My Quizz'))
                        ->to($user->getEmail())
                        ->subject('Please Confirm your New Email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );

                return $this->redirectToRoute('app_logout');
            }

            return $this->render('user/connected/change_email.html.twig', [
                'form' => $form->createView(),
            ]);
        } else {

            return $this->render('app/index.html.twig');
        }
    }

    #[Route('/change-password', name: 'app_change_password')]
    public function changePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {   
        if ($this->getUser()) {
            $user = $this->getUser();

            $form = $this->createForm(ChangePasswordType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $oldPassword = $form->get('oldPassword')->getData();
                $newPassword = $form->get('newPassword')->getData();


                if (!$userPasswordHasher->isPasswordValid($user, $oldPassword)) {

                    $this->addFlash('error', 'Mot de passe actuel incorrect');
                    return $this->redirectToRoute('app_change_password');
                }

                $hashedPassword = $userPasswordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);

                $entityManager->flush();

                return $this->redirectToRoute('app_logout');
            }

            return $this->render('user/connected/change_password.html.twig', [
                'form' => $form->createView(),
            ]);
        } else {
            return $this->render('app/index.html.twig');
        }
    }
}
