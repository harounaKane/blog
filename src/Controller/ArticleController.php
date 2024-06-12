<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\ArticleType;
use App\Form\CommentaireType;
use App\Repository\ArticleRepository;
use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArticleController extends AbstractController
{
    #[Route('/admin/article', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/admin/article/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try{

                /** @var UploadedFile $brochureFile */
                $image = $form->get('image')->getData();

                // condition pour vérifier qu'on a uploadé une image
                if ($image) {
                    $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    // inclure le nom du fichier
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                    // Déplacement du fichier dans son répertoire de destination
                    try {
                        $image->move("logo_repertoire", $newFilename);
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $article->setLogo($newFilename);
                }

                $article->setCreateAt(new \DateTimeImmutable());
                $entityManager->persist($article);
                $entityManager->flush();

                $this->addFlash("success", "L'article '". $article->getTitre()."' est ajouté");
    
                return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
            }catch(UniqueConstraintViolationException $e){
                $this->addFlash("warning", "Le nom de cet article '". $article->getTitre()."' existe déjà");
            }
        }
           

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/article/{id}', name: 'app_article_show', methods: ['GET', 'POST'])]
    public function show(Article $article, Request $req, EntityManagerInterface $manager): Response
    {
        //FORMULAIRE COMMENTAIRE
        $comment = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $comment);
        $form->handleRequest($req);

        if( $form->isSubmitted() && $form->isValid() ){
            $comment->setCreateAt(new \DateTimeImmutable());
            $comment->setArticle($article);

            try{
                $manager->persist($comment);
                $manager->flush();

                return $this->redirectToRoute("app_article_show", ['id' => $article->getId()]);

            }catch(UniqueConstraintViolationException $u){
                
            }
        }


        return $this->render('article/show.html.twig', [
            'article' => $article,
            "form" => $form
        ]);
    }

    #[Route('/admin/article/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/admin/article/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
