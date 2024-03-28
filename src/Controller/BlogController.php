<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog')]
    public function index(PostRepository $postRepository): Response
    {
        // Récupérer les posts triés par ordre décroissant en fonction de leur ID
        $posts = $postRepository->findBy([], ['datePost' => 'DESC']);

        return $this->render('blog/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
    public function new(Request $req, ManagerRegistry $doc): Response
    {
        if ($req->isXmlHttpRequest()) {
            $post = new Post();
            $caption = $req->get('caption');
            $fichierImage = $req->files->get('image');
            $post->setCaption($caption);
            $post->setImageFile($fichierImage);
            $post->setDatePost(new DateTime());


            $em = $doc->getManager();
            $em->persist($post);
            $em->flush();
            return new JsonResponse(['success' => true]);
        }
        return $this->redirectToRoute('app_blog');
    }

    #[Route('/blog/{id}', name: 'app_blog_delete', methods: ['DELETE'])]
    public function delete(ManagerRegistry $doctrine, $id, PostRepository $postRepository, Request $req): Response
    {
        if ($req->isXmlHttpRequest()) {
            $auteur = $postRepository->find($id);
            $em = $doctrine->getManager();
            $em->remove($auteur);
            $em->flush();
            return new Response('Post supprimé avec succès', Response::HTTP_OK);
        }
        return $this->redirectToRoute('app_blog');
    }

    #[Route('/blog/edit/{id}', name: 'app_blog_update', methods: ['PUT'])]
    public function update(ManagerRegistry $doctrine, $id, Request $req): Response
    {
        if ($req->isXmlHttpRequest()) {
            $entityManager = $doctrine->getManager();
            $post = $entityManager->getRepository(Post::class)->find($id);

            if (!$post) {
                return new JsonResponse(['error' => 'Le post avec l\'identifiant ' . $id . ' n\'existe pas.'], Response::HTTP_NOT_FOUND);
            }

            // Récupérer les données envoyées depuis la requête AJAX
            $requestData = json_decode($req->getContent(), true);

            // Mettre à jour les champs du post
            $post->setCaption($requestData['caption']);
            // Ajouter d'autres champs à mettre à jour si nécessaire

            // Enregistrer les modifications en base de données
            $entityManager->flush();

            // Répondre avec un message de succès
            return new JsonResponse(['message' => 'Le post a été mis à jour avec succès.'], Response::HTTP_OK);
        }

        return $this->redirectToRoute('app_blog');
    }

}
