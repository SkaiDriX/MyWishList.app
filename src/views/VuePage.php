<?php

namespace mywishlist\views;

class VuePage extends Vue
{
  public function __construct($data, $container)
  {
    parent::__construct($data, $container);
  }

  public function render($i = null)
  {
    $this->content = $this->accueil();
    $this->titre_page = "Accueil";

    return parent::render();
  }

  /*-------------------------------------------------------------------------------------------*/
  /* MÉTHODE(S) POUR LA PAGE D'ACCUEIL */
  /*-------------------------------------------------------------------------------------------*/

  /**
   * On récupère l'ensemble des listes publiques
   */
  private function listePublique(): string
  {
    $html = "";

    foreach ($this->data as $data) {
      $titre = $data['titre'];

      $description = $data['description'];

      if(strlen($description) > 70) {
        $description = substr_replace($data['description'], "... <b>[Lire pour voir plus]</b>", 70);
      }

      $link = $this->container->router->pathFor('affichage_liste', [
        'tokenPublic' => $data['token'],
      ]);

      $content = <<<FIN
        <div class="card my-4">
          <h5 class="card-header">$titre</h5>
          <div class="card-body">
            <p class="card-text" style="font-size: 0.8em;">$description</p>
            <div><a href="$link" class="btn btn-primary pull-right" style="line-height: 1; float:right;">Voir la liste</a></div>
          </div>
        </div>
FIN;

      $html = $html . $content;
    }

    return $html;
  }

  /**
   * O
   */
  private function accueil(): string
  {
    $listePublique = $this->listePublique();

    $html = <<<FIN
  <h1 class="my-4 text-center">Accueil</h1>

  <div class="row">
    <div class="col-md-8">
      <div class="card mb-4">
        <h5 class="card-header">Comment ça marche ?</h5>
        <div class="card-body">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
          Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. 
          Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. 
          Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <h5 class="text-center">Les liste publiques</h5>
      $listePublique
    </div>
  </div>
FIN;

    return $html;
  }


}
