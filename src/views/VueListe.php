<?php

namespace mywishlist\views;

class VueListe extends Vue
{
  public function __construct($data, $container)
  {
    parent::__construct($data, $container);
  }

  private function formulaireEdition(): string
  {
    // Les variables requises
    $url_postEdition = $this->container->router->pathFor('edition_liste_post', [
      'tokenPublic' => $this->data['clePublique'],
      'tokenPrivate' => $this->data['clePrive'],
    ]);

    $nomliste = $this->data['liste']->titre;
    $descliste = $this->data['liste']->description;
    $visibility = $this->data['liste']->publique;
    
    $statusPublique = "";
    $statusPrive = "";

    if($visibility == 0) {
      $statusPrive = "checked";
    } else {
      $statusPublique = "checked";
    }

    // L'affichage
    $html = <<<FIN
<form method="POST" action="$url_postEdition">
    <label>Nom de la liste :<br> <textarea rows="5" cols="33" name="titre">$nomliste</textarea></label><br>
    <label>Description de la liste :<br> <textarea rows="5" cols="33" name="desc">$descliste</textarea></label><br>
    <label>Visibilité de la liste :<br>
      <div>
        <input type="radio" name="visibility" value="public" $statusPublique>
        <label for="public">Publique</label>
      </div>
      <div>
        <input type="radio" name="visibility" value="private" $statusPrive>
        <label for="private">Privée</label>
      </div>
    </label><br>
	<button type="submit">Modifier</button>
</form>	
FIN;
    return $html;
  }


  private function formulaireCreation(): string
  {
    // Les variables requises
    $url_postCreation = $this->container->router->pathFor('create_liste_post');

    // L'affichage
    $html = <<<FIN
    <div class="container-fluid bg-secondary bg-gradient" style="height: 100px">
    <span class="container-fluid align-middle">
        <h1 class="text-white fs-1 align-middle ps-3">Cr&eacute;er une liste</h1>
    </div>
    </div>
    <div style="margin-left: 3%;">
    <form method="POST" action="$url_postCreation">
        <label><br><h5>Nom de la liste :</h5> <textarea rows="1" cols="33" name="titre" class="form-control"></textarea></label><br>
        <label><br><h5>Description de la liste :</h5> <textarea rows="5" cols="55" class="form-control" name="desc"></textarea></label><br>
        <label><br><h5>Date limite :</h5> <input type="date" name="date" class="form-control" required></label><br><br><br>
      <button type="submit" class="btn btn-success" style="padding-left: 50px; padding-right: 50px;">Cr&eacute;er</button>
      <button type="button" class="btn btn-outline-danger" style="padding-left: 50px; padding-right: 50px;">Retour</button>
    </form>	
</div>
FIN;
    return $html;
  }

  private function voirListe(): string
  {
    // Les variables requises
    $liste = $this->data['liste'];

    // L'affichage
    $html = "
    Titre : " . $liste->titre . "<br>
    Description : " . $liste->description . "<br>";

    if ($this->data['isOwner'] == 1) {
      $html = "Vous êtes le proprio <br>" . $html;
    }

    return $html;
  }

  public function render($i = null)
  {

    switch ($i) {
      case 1:
        $this->content = $this->formulaireEdition();
        $this->titre_page = "Édition d'une liste";
        break;
      case 2:
        $this->content = $this->formulaireCreation();
        $this->titre_page = "Création d'une liste";
        break;
      case 3:
        $this->content = $this->voirListe();
        $this->titre_page = "Voir une liste";
        break;
    }

    return parent::render();
  }
}
