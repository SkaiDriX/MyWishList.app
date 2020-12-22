<?php

namespace mywishlist\views;

class VuePage extends Vue
{
  public function __construct($data, $container)
  {
    parent::__construct($data, $container);
  }

  private function listePublique(): string
  {
    $html = "<ul>";

    foreach ($this->data as $data) {
      $html = $html . "<li>" . $data['titre'] . "</li>";
    }

    $html = $html . "</ul>";
    return $html;
  }

  public function render($i = null)
  {
    $this->content = $this->listePublique();
    $this->titre_page = "Accueil";

    return parent::render();
  }
}
