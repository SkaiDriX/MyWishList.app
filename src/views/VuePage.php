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
   * Méthode pour l'affichage de l'accueil
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
          Bienvenue sur MyWishList, un site de gestion de liste de souhait !</br></br>
          <b>Voici quelques fonctionnalités :</b></br>
          <ul>
            <li>Système de liste de souhaits : création / modification :</li>
            <li>Système d'items sur les listes : création / modification / suppression / réservation </li>
            <li>Affichage de liste de souhaits, affichage des items</li>
            <li>Ajout de message sur les liste de souhaits</li>
            <li>Partage de liste de souhaits</li>
            <li>Liste de souhaits publiques / privées</li>
            <li>Système de compte : inscription / connexion / déconnexion</li>
          </ul>
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
