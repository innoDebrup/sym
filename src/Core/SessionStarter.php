<?php

namespace App\Core;

use Symfony\Component\HttpFoundation\Session\Session;

class SessionStarter {
  public static function startNewSession() {
    $session = new Session();
    $session->start();
  }
}