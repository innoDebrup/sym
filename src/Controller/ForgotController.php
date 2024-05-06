<?php

namespace App\Controller;

use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\SendMail;
use DateTimeImmutable;
use App\Services\Validate;

class ForgotController extends AbstractController {
  
  /**
   * Entity Manager Interface for Forgot controller.
   *
   * @var Doctrine\ORM\EntityManagerInterface
   */
  private $entity_manager;

  /**
   * Constructor to initialize ForgotController.
   *
   * @param Doctrine\ORM\EntityManagerInterface $em
   *   Entity Manager Interface for Forgot controller.
   */
  public function __construct(EntityManagerInterface $em){
    $this->entity_manager = $em;
  }

  /**
   * Index function to execute for forgot route.
   *
   * @param Symfony\Component\HttpFoundation\Request $req
   *   Request object
   * 
   * @return Response | RedirectResponse
   *   Renders a twig as the response initially on get request.
   *   Redirects to redirect route on success or error route for other cases.
   */
  #[Route('/forgot', name: 'app_forgot')]
  public function index(Request $req): Response|RedirectResponse {
    if ($req->getMethod() == 'POST') {
      $entity_manager = $this->entity_manager;
      $users = $entity_manager->getRepository(Users::class);
      $email = $req->request->get('email');
      if ($users->findOneBy(['email' => $email])) {
        $token = bin2hex(random_bytes(16)); 
        $token_hash = hash('sha256',$token);
        $expiry = date('Y-m-d H:i:s', time() + 60 * 10);
        $exp = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $expiry);
        $user = $users->findOneBy(['email' => $email]);
        $user->setResetToken($token_hash);
        $user->setTokenTimer($exp);

        $entity_manager->persist($user);
        $entity_manager->flush();

        $send_mail = new SendMail();
        $send_mail->setContent("http://sym.com/reset?token=$token_hash");
        $send_mail->sendResetMail($email);

        return $this->redirectToRoute('app_redirect',[
          'origin' => 'forgot',
          'message' => 'Reset Password Link has been sent to your mail!'
        ]);
      }
      return $this->redirectToRoute('app_error', [
        'origin' => 'forgot',
        'message' => 'Invalid Mail!'
      ]);
    }
    return $this->render('forgot/index.html.twig', [
      'message' => ''
    ]);
  }

  /**
   * Reset function to reset password of the user.
   *
   * @param Symfony\Component\HttpFoundation\Request $req
   *   Request object
   * 
   * @return Response | RedirectResponse
   *   Renders a twig as the response initially on get request.
   *   Redirects to redirect route on success or error route for other cases.
   */
  #[Route('/reset', name: 'app_reset')]
  public function reset(Request $req): Response | RedirectResponse { 
    $message = '';
    if (!empty($req->query->get('token',''))) {
      $entity_manager = $this->entity_manager;
      $token = $req->query->get('token');
      $users = $entity_manager->getRepository(Users::class);
      $user = $users->findOneBy(['reset_token' => $token]);
      if ($user) {
        $expiry_time = $user->getTokenTimer();
        $expiry_time = strtotime($expiry_time->format('Y-m-d H:i:s'));
        if (time() < $expiry_time) {
          if($req->getMethod() == 'POST') {
            $password = $req->request->get('password');
            $validator = new Validate();
            if($validator->validPassword($password)) {
              $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
              $entity_manager->persist($user);
              $entity_manager->flush();
            
              return $this->redirectToRoute('app_redirect', [
                'origin' => 'reset',
                'message' => 'Password Reset Successfully! Please Login!'
              ]);
            }
            $message = $validator->getPasswordErr() . ' Please Retry!';
            return $this->redirectToRoute('app_error', [
              'origin' => 'reset',
              'message' => $message
            ]);
          }
          return $this->render('reset/index.html.twig');
        }
        $message = 'Token expired! Please retry Forgot password again!';
      }
      else {
        $message = 'Invalid Token! Please retry Forgot password again!';
      }
    }
    else{
      $message = 'Invalid Link! Please retry Forgot password again!';
    }
    return $this->redirectToRoute('app_error', [
      'origin' => 'reset',
      'message' => $message
    ]);
  }

  /**
   * Redirect function to handle all success redirects for ForgotController.
   *
   * @param Symfony\Component\HttpFoundation\Request $req
   *   Request object
   * 
   * @return Response | RedirectResponse
   *   Renders a twig as the response initially on appropriate get request.
   *   Redirects to login route for other cases.
   */
  #[Route('/redirect', name: 'app_redirect')]
  public function resetpass(Request $req): Response|RedirectResponse{
    $origin = $req->query->get('origin');
    $message = $req->query->get('message');
    if ($req->getMethod() == 'POST' || empty($origin) || empty($message)){
      return $this->redirectToRoute('app_login');
    }
    if($origin == 'reset'|| $origin == 'forgot'){
      return $this->render('redirect/index.html.twig', [
        'message' => $message
      ]);
    }
    return $this->redirectToRoute('app_login');
  }
}
