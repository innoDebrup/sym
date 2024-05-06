<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CustomExtension extends AbstractExtension {
  public function getFilters() {
    return [
      new TwigFilter('base64_encode', [$this, 'base64_en']),
    ];
  }

  public function base64_en($input) {
    return base64_encode((string)$input);
  }
}
