<?php

namespace App\Security;

use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler extends AbstractController implements AccessDeniedHandlerInterface
{
    private $entityManager;
    private $categorieRepository;

    public function __construct(EntityManagerInterface $entityManager, CategorieRepository $categorieRepository)
    {
        $this->entityManager = $entityManager;
        $this->categorieRepository = $categorieRepository;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        
        return $this->render('app/index.html.twig', [
            'categories' => $this->categorieRepository->findAll()
        ]);
    }
}
