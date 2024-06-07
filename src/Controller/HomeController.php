<?php

namespace App\Controller;

use App\Entity\Commentire;
use App\Form\CommentaireType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $artRepo): Response
    {
         
        return $this->render('home/index.html.twig', [
            'articles' => $artRepo->findAll()
        ]);
    }
}
