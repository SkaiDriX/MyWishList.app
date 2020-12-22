<?php

namespace mywishlist\views;

class VueListe
{
  private $args;
  private $container;

  public function __construct($args, $container)
  {
    $this->args = $args;
    $this->container = $container;
  }

  private function formulaireEdition(): string
  {
    $url_postEdition = $this->container->router->pathFor('edition_liste_post', [
      'tokenPublic' => $this->args['clePublique'],
      'tokenPrivate' => $this->args['clePrive'],
    ]);

    $nomliste = $this->args['liste']->titre;
    $descliste = $this->args['liste']->description;

    $html = <<<FIN
<form method="POST" action="$url_postEdition">
    <label>Nom de la liste :<br> <textarea rows="5" cols="33" name="titre">$nomliste</textarea></label><br>
    <label>Description de la liste :<br> <textarea rows="5" cols="33" name="desc">$descliste</textarea></label><br>
    <label>Visibilité de la liste :<br>
      <div>
        <input type="radio" id="public" name="visibility" value="public">
        <label for="public">Publique</label>
      </div>
      <div>
        <input type="radio" id="private" name="visibility" value="private" checked>
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
    $url_postCreation = $this->container->router->pathFor('create_liste_post');

    $html = <<<FIN
<form method="POST" action="$url_postCreation">
  <label>Nom de la liste :<br> <textarea rows="5" cols="33" name="titre"></textarea></label><br>
  <label>Description de la liste :<br> <textarea rows="5" cols="33" name="desc"></textarea></label><br>
  <label>Description de la liste :<br> <input type="date" name="date" required></label><br>
<button type="submit">Créer</button>
</form>	
FIN;
    return $html;
  }

  private function voirListe(): string
  {
    $liste = $this->args['liste'];

    $html = "
    Titre : ".$liste->titre."<br>
    Description : ".$liste->description."<br>";

    if($this->args['isOwner'] == 1) {
      $html = "Vous êtes le proprio <br>" . $html;
    }

    return $html;
  }

  public function render($i)
  {

    switch ($i) {
      case 1:
        $content = $this->formulaireEdition();
        $titre = "Édition d'une liste";
        break;
      case 2:
        $content = $this->formulaireCreation();
        $titre = "Édition d'une liste";
        break;
      case 3:
        $content = $this->voirListe();
        $titre = "Voir la liste";
        break;
    }

    $html = <<<FIN
        <!DOCTYPE html>
        <html>
          <head>
            <title>Exemple</title>
          </head>
          <body>
            <h1>$titre</h1>
            $content
          </body>
        </html>
FIN;
    return $html;
  }
}
