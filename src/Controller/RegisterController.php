<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Core\Validate;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RegisterController extends AbstractController {
  #[Route('/register', name: 'app_register')]
  public function index(Request $req): RedirectResponse|Response {
    $session = $req->getSession();
    //dd($session);
    $not_duplicate = $valid_password = $valid_input = TRUE;
    $success = FALSE;
    $message = $user_name = $email = $password = $otp = $entered_otp = '';
    $validator = new Validate();
    if ($req->getMethod() == 'POST') {
      if (!empty($session->get('otp',''))) {
        $user_name = htmlspecialchars($req->request->get('user_name'));
        $email = $session->get('email');
        $password = htmlspecialchars($req->request->get('password'));
        $otp = $session->get('otp');
        $entered_otp = $req->request->get('otp');
        if (!empty($user_name) && !empty($email) && !empty($password)) {
          // $not_duplicate = $read->checkUser($user_name, $email);
          $valid_password = $validator->validPassword($password);
          if ($not_duplicate && $valid_password) {
            if ($entered_otp == $otp) {
              // $create->addUser($user_name, $email, $password);
              $message = 'User Created Successfully!! Please proceed to Login!';
              $success = TRUE;
            } 
            else {
              $message = 'Wrong OTP! Please Retry!!';
            }
          } 
          elseif (!$not_duplicate) {
            $message = 'Username already exists!!! Please retry with another';
          } 
          else {
            $message = $validator->getPasswordErr();
          }
        } 
        else {
          $valid_input = FALSE;
        }
      } 
      else {
        $message = "Please verify your email through OTP by clicking Get OTP after entering your email address";
        $valid_input = FALSE;
        return $this->redirectToRoute('app_error', [
          'origin' => 'register',
          'message' => $message,
          'valid_input' => $valid_input
        ]);
      }
      $session->clear();
    }
    return $this->render('register/index.html.twig', [
      'valid_input' => $valid_input,
      'message'=> $message,
      'success' => $success
    ]);
  }
}
