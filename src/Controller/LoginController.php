<?php

namespace App\Controller;

use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginController extends AbstractController {
  /**
   * Entity Manager Interface for Register controller
   *
   * @var Doctrine\ORM\EntityManagerInterface
   */
  private $entity_manager;
  
 /**
   * Constructor to innitialize all the variables.
   *
   * @param Doctrine\ORM\EntityManagerInterface $em
   *   Entity Manager Interface for Register controller.
   */
  public function __construct(EntityManagerInterface $em){
    $this->entity_manager = $em;
  }

  /**
   * Function to handle login functionality.
   *
   * @param  Symfony\Component\HttpFoundation\Request $req
   *   Request object to store all http request attributes.
   * 
   * @return Response | RedirectResponse
   *   Renders the twig as the response for get requests.Redirects to home on 
   *   successful checks else redirects to error for failed checks.
   */
  #[Route('/login', name: 'app_login')]
  public function index(Request $req): Response|RedirectResponse {
    $session = $req->getSession();
    // Redirect to home if already logged in.
    if ($session->get('username')) {
      return $this->redirectToRoute('app_home');
    } 
    // Process if request methos is a GET.
    if ($req->getMethod() == 'GET') {
      $message = $req->query->get('message');
      return $this->render('login/index.html.twig', [
        'message' => $message
      ]);
    }
    // Handles POST request form data.
    $entity_manager = $this->entity_manager;
    $post = $req->request;
    $user_mail = $post->get('user_mail');
    $password = $post->get('password');
    $users = $entity_manager->getRepository(Users::class);
    $user = NULL;
    if ($users->findOneBy(['user_name' => $user_mail])) {
      $user = $users->findOneBy(['user_name' => $user_mail]);
    }
    else if ($users->findOneBy(['email' => $user_mail])) {
      $user = $users->findOneBy(['email' => $user_mail]);
    }
    if ($user) {
      if (password_verify($password, $user->getPassword())) {
        $session->set('username', $user->getUserName());
        $session->set('userid', $user->getId());
        return $this->redirectToRoute('app_home');
      }
    }
    return $this->redirectToRoute('app_error', [
      'origin' => 'login',
      'message' => 'Invalid Username/Email or Password!!'
    ]);
  }

  /**
   * Function to log the user out of the session.
   *
   * @param Symfony\Component\HttpFoundation\Request $req
   *   Request object to store all http request attributes.
   * 
   * @return Response | RedirectResponse
   *   Returns a twig as response if successfully logged out. Redirects to
   *   login if already logged out.
   */
  #[Route('/logout', name: 'app_logout')]
  public function logout(Request $req): Response | RedirectResponse {
    $session = $req->getSession();
    if ($session->get('username')) {
      $session->clear();
      return $this->render('logout/index.html.twig');
    }
    return $this->redirectToRoute('app_login');
  }
}
