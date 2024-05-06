<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;
use App\Entity\Posts;
use App\Entity\Likes;
use App\Services\FileUploader;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\PostsRepository;

class HomeController extends AbstractController {
  /**
   * Entity Manager Interface for Register controller
   *
   * @var Doctrine\ORM\EntityManagerInterface
   */
  private $entity_manager;

  /**
   * Repository of all valid objects for Posts entity class.
   *
   * @var App\Repository\PostsRepository
   */
  private $posts_repo;
  
  /**
   * Constructor to innitialize all the variables.
   *
   * @param Doctrine\ORM\EntityManagerInterface $em
   *   Entity Manager Interface for Register controller.
   * @param App\Repository\PostsRepository $p
   *   Repository of all valid objects for Posts entity class.
   */
  public function __construct(EntityManagerInterface $em, PostsRepository $p){
    $this->entity_manager = $em;
    $this->posts_repo = $p;

  }

  /**
   * Function to call for home route.
   *
   * @param Symfony\Component\HttpFoundation\Request $req
   *   Request object to store all http request attributes.
   * @param SluggerInterface $slugger
   *   SluggerInterface object to generate and store valid/safe filenames.
   * 
   * @return Response | RedirectResponse
   *   Redirects to login if not signed in. Renders a twig as the response for
   *   valid cases.
   */
  #[Route('/home', name: 'app_home')]
  public function index(Request $req, SluggerInterface $slugger): Response|RedirectResponse {
    $entity_manager = $this->entity_manager;
    $session = $req->getSession();
    if (!$session->get('username')){
      return $this->redirectToRoute('app_login');
    }
    $user_name = $session->get('username');
    $post_err_msg = "";
    if ($req->getMethod() == 'POST') {
      $post = $req->request;
      $files = $req->files;
      $content = $post->get('content');
      if (!empty($content) && strlen($content) < 1024) {
        $post_ob = new Posts();
        $post_ob->setContent($content);
        $user = $entity_manager->getRepository(Users::class)->findOneBy(['user_name' => $user_name]);
        $post_ob->setUser($user);
        $media_obj = $files->get('media');
        if ($media_obj) {
          $media_type = $media_obj->getMimeType();
          $uploader = new FileUploader($slugger);
          $uploader->setTargetDirectory($this->getParameter('kernel.project_dir').'/public/uploads');
          $media = $uploader->upload($media_obj);
          $media_path = 'uploads/' . $media;
          $post_ob->setMediaType($media_type);
          $post_ob->setMedia($media_path);
        }
        $entity_manager->persist($post_ob);
        $entity_manager->flush();

        $user->addPost($post_ob);
        $entity_manager->persist($user);
        $entity_manager->flush();
      }
      else {
        $post_err_msg = "<h3>You cannot post without Writing something! Write something :)</h3>";
        if (strlen($content) >= 1024) {
          $post_err_msg = "<h3>Character limit of 1024 exceeded. Please write within 1024 characters!</h3>";
        }
      }
      return $this->json([
        'posterror' => $post_err_msg
      ]);
    }
    return $this->render('home/index.html.twig', [
      'user_name' => $user_name,
      'post_err' => $post_err_msg
    ]);
  }

  /**
   * Function to like a post.
   *
   * @param Symfony\Component\HttpFoundation\Request $req
   *   Request object to store all http request attributes.
   * 
   * @return Response | RedirectResponse
   *   Returns json response if request method is post. For all other cases 
   *   redirects to home.
   */
  #[Route('/like', name: 'app_like')]
  public function like(Request $req): Response | RedirectResponse {
    $entity_manager = $this->entity_manager;
    if ($req->getMethod() == 'POST') {
      $post = $req->request;
      $post_id = $post->get('post_id');
      $session = $req->getSession();
      $user_id = $session->get('userid');
      $post = $entity_manager->getRepository(Posts::class)->findOneBy(['id' => $post_id]);
      $user = $entity_manager->getRepository(Users::class)->findOneBy(['id' => $user_id]);
      $flag = 0;
      $likes = $post->getLikes()->getValues();
      $total = count($likes);
      $target = NULL;
      foreach ($likes as $like) {
        if ($like->getUser()->getId() == $user_id) {
          $flag = 1;
          $target = $like;
          break;
        }
      }

      if ($flag) {
        $post->removeLike($target);
        $user->removeLike($target);
        $entity_manager->remove($target);
        $entity_manager->flush();
        $status = 'removed';
        $total -= 1;
      }
      else {
        $like = new Likes();
        $like->setPost($post);
        $like->setUser($user);
        $entity_manager->persist($like);
        $entity_manager->flush();
        $status = 'added';
        $total += 1;
      }
      return $this->json([
        'total' => $total,
        'user_name' => $user_id,
        'status' => $status
      ]);
    }
    return $this->redirectToRoute('app_home');;
  }

  /**
   * Function to load posts.
   *
   * @param Symfony\Component\HttpFoundation\Request $req
   *   Request object to store all http request attributes.
   * 
   * @return Response | RedirectResponse
   *   Returns json response if request method is post. For all other cases
   *   redirects to home.
   */
  #[Route('/load', name: 'app_load')]
  public function load(Request $req): Response | RedirectResponse { 
    $session = $req->getSession();
    if ($req->getMethod() == 'POST') {
      $posts_repo =$this->posts_repo;
      $liked = [];
      $posts = $posts_repo->findLimitedEntities(2,$req->request->get('offset'));
      foreach($posts as $post){
        $likes = $post->getLikes()->getValues();
        $pass = 0;
        foreach ($likes as $like) {
          if ($like->getUser()->getId() == $session->get('userid')) {
            $liked[] = 1;
            $pass = 1;
            break;
          }
        }
        if (!$pass) {
          $liked[] = 0;
        }
      }
      $html = $this->renderView('load/index.html.twig', [
        'posts' => $posts,
        'liked' => $liked
      ]);
      return $this->json(['html' => $html]);
    }
    return $this->redirectToRoute('app_home');
  }
}
