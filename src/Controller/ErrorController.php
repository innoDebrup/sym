<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ErrorController extends AbstractController
{ 
  /**
   * Index function to Error route.
   *
   * @param Symfony\Component\HttpFoundation\Request $req
   *    Request object.
   * 
   * @return Response | RedirectResponse
   *   For invalid access it redirects to login route. 
   *   Else it displays the error message received.
   */
  #[Route('/error', name: 'app_error')]
  public function index(Request $req): Response | RedirectResponse { 
    $get = $req->query;

    if ($req->getMethod() == 'POST' || empty($get->get('origin') || empty($get->get('messsage')))){
      return $this->redirectToRoute('app_login');
    }
    if ($get->get('origin') == 'register') {
      $valid_input = $get->get('valid_input');
      $message = $get->get('message');
      return $this->render('error/register.html.twig', [
        'valid_input' => $valid_input,
        'message'=> $message
      ]);
    }
    if ($get->get('origin') == 'login') {
      return $this->render('error/display.html.twig', [
        'message' => $get->get('message'),
        'origin' => $get->get('origin')
      ]);
    }
    if ($get->get('origin') == 'forgot') {
      return $this->render('error/display.html.twig', [
        'message' => $get->get('message'),
        'origin' => $get->get('origin')
      ]);
    }
    if ($get->get('origin') == 'reset') {
      return $this->render( 'error/display.html.twig', [
        'message' => $get->get('message'),
        'origin' => $get->get('origin')
      ]);
    }
    return $this->redirectToRoute('app_login');
  }
}
