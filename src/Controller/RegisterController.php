<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Services\Validate;
use App\Entity\Users;
use App\Services\SendMail;

class RegisterController extends AbstractController {
  
  /**
   * Entity Manager Interface for Register controller
   *
   * @var Doctrine\ORM\EntityManagerInterface
   */
  private $entity_manager;
  
  /**
   * Constructor to initialize variables.
   *
   * @param Doctrine\ORM\EntityManagerInterface $em
   *   Entity Manager Interface for Register controller
   */
  public function __construct(EntityManagerInterface $em){
    $this->entity_manager = $em;
  }

  /**
   * Function to handle Registration process form data.
   *
   * @param Request $req
   *  The HTTP Request object.
   * 
   * @return RedirectResponse | Response
   *  Redirects if post method is used and the destination is determined 
   *  according to the processing of the received data. If no post method is 
   *  used then it returns a Response as a twig render for form display.
   */
  #[Route('/register', name: 'app_register')]
  public function index(Request $req): RedirectResponse|Response {
    $entity_manager = $this->entity_manager;
    $not_duplicate = $valid_password = $valid_input = TRUE;
    $success = $error = FALSE;
    $message = $user_name = $email = $password = $otp = $entered_otp = '';
    $validator = new Validate();
    if ($req->getMethod() == 'POST') {
      $users = $entity_manager->getRepository(Users::class);
      $session = $req->getSession();
      if (!empty($session->get('otp',''))) {
        $user_name = htmlspecialchars($req->request->get('user_name'));
        $email = $session->get('email');
        $password = htmlspecialchars($req->request->get('password'));
        $otp = $session->get('otp');
        $entered_otp = $req->request->get('otp');
        if (!empty($user_name) && !empty($email) && !empty($password)) {
          $not_duplicate = !($users->findOneBy(['user_name' => $user_name]));
          $valid_password = $validator->validPassword($password);
          if ($not_duplicate && $valid_password) {
            if ($entered_otp == $otp) {
              $user = new Users();
              $user->setUserName($user_name);
              $user->setEmail($email);
              $user->setPassword(password_hash($password,PASSWORD_DEFAULT));
              $entity_manager->persist($user);
              $entity_manager->flush();
              $success = TRUE;
            } 
            else {
              $message = 'Wrong OTP! Please Retry!!';
              $error = TRUE;
            }
          } 
          elseif (!$not_duplicate) {
            $message = 'Username already exists!!! Please retry with another';
            $error = TRUE;
          } 
          else {
            $message = $validator->getPasswordErr();
            $error = TRUE;
          }
        } 
        else {
          $valid_input = FALSE;
          $error = TRUE;
        }
      } 
      else {
        $message = "Please verify your email through OTP by clicking Get OTP after entering your email address";
        $valid_input = FALSE;
        $error = TRUE;
      }
      $session->clear();
    }
    if ($error) {
      return $this->redirectToRoute('app_error', [
        'origin' => 'register',
        'message' => $message,
        'valid_input' => $valid_input
      ]);
    }
    elseif ($success) {
      return $this->redirectToRoute('app_login', [
        'message'=> 'Registered Successfully! Please Login!',
      ]);
    }
    else {
      return $this->render('register/index.html.twig', [
        'valid_input' => $valid_input,
        'message'=> $message,
        'success' => $success
      ]);
    }
  }
  
  /**
   * Function to handle otp processing.
   *
   * @param Request $req
   *   The HTTP Request object.
   * 
   * @return Response
   *   Returns json response.
   */
  #[Route('/otp', name: 'app_otp')]
  public function otp(Request $req): Response {
    $session = $req->getSession();
    $post = $req->request;
    $otp = rand(1000, 9999);
    $email = $post->get('email');
    $validator = new Validate();
    $entity_manager = $this->entity_manager;
    $isPresent = (bool) $entity_manager->getRepository(Users::class)->findOneBy(['email' => $email]);
    if( $validator->validEmail($email) && !$isPresent) {
      $session->set('otp', (string) $otp);
      $session->set('email', $email );
      $session->set('expiry', time() + 60 * 1);
      $send_mail = new SendMail();
      $send_mail->setContent($otp);
      $send_mail->sendOTPMail($email);
      return $this->json([
        'message' => '<h3>OTP sent! Please Check your Mail!</h3>',
        'valid' => 1
      ],200);
    }
    if ($isPresent) {
      return $this->json([
        'message' => '<h3>Email is already Registered!</h3>',
        'valid' => 0
      ],200);
    }
    return $this->json([
      'message' => '<h3>Invalid Email! Please provide a valid one!</h3>',
      'valid' => 0
    ],200);
  }
}
