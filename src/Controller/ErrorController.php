<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ErrorController extends AbstractController
{
  #[Route('/error', name: 'app_error')]
  public function index(Request $req): Response { 
    $get = $req->query;
    if ($get->get('origin') == 'register') {
      $valid_input = $get->get('valid_input');
      $message = $get->get('message');
      return $this->render('error/register.html.twig', [
        'valid_input' => $valid_input,
        'message'=> $message,
      ]);
    }
    return $this->render('error/index.html.twig', [
      'controller_name' => 'ErrorController',
    ]);
  }
}
