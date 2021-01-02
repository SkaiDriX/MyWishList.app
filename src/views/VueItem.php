<?php

namespace mywishlist\views;

class VueItem extends Vue
{

  public function __construct($data, $container)
  {
    parent::__construct($data, $container);
  }

  public function render($i = null)
  {

    switch ($i) {
      case 1:
        $this->content = $this->formulaireEdition();
        $this->titre_page = "Modification d'un item";
        break;
      case 2:
        $this->content = $this->formulaireCreation();
        $this->titre_page = "Création d'un item";
        break;
      case 3:
        $this->content = $this->voirItem();
        $this->titre_page = "Affichage d'un item";
        break;
    }

    return parent::render();
  }

  /*-------------------------------------------------------------------------------------------*/
  /* MÉTHODE(S) POUR LA PARTIE affichage */
  /*-------------------------------------------------------------------------------------------*/

  private function getReservation() {
    if($this->data['reserved']) {

      $nom = $this->data['reservation']->nom;
      $msg = $this->data['reservation']->message;

      $content = <<<FIN
      <p>L'item est déjà reservé par <b>$nom</b> !</p>
      <p><b>Message de la réservation : </b></br>$msg</p>
FIN;
    } else {
      $url_reservation = $this->container->router->pathFor('reservation_item', [
        'tokenPublic' => $this->data['public'],
        'idItem' => $this->data['item']->id
      ]);

      $identite = $this->data['identite'];

      $content = <<<FIN
      <form method="POST" action="$url_reservation">
        <textarea rows="4" cols="55" class="form-control" name="message" ></textarea>
        <div class="input-group mb-3 mt-4">
          <span class="input-group-text">Pseudo</span>
          <input type="text" name="identite" class="form-control" value="$identite">
        </div>
        <div class="d-flex justify-content-center">
          <button class="btn btn-success">Réserver l'item</button>
        </div>
      </form>
FIN;
    }

    return $content;
  }

  private function voirItem(): string
  {
    // Les variables requises
    $url_liste = $this->container->router->pathFor('affichage_liste', [
      'tokenPublic' => $this->data['public']
    ]);

    $nom = $this->data['item']->nom;
    $desc = $this->data['item']->descr;
    $img = $this->data['item']->img;
    $url = $this->data['item']->url;
    $tarif = $this->data['item']->tarif;

    $reservation = $this->getReservation();

    if($url == "") {
      $url="Aucune renseignée";
    }

    // L'affichage
    $html = <<<FIN
    <div class="my-4 d-flex justify-content-center align-self-center flex-column text-center">
  <h1>$nom</h1>  
  <div class="align-self-center mt-2">
  <a href="$url_liste" class="btn btn-outline-secondary">Retour à la liste</a>
</div>
</div>

    <div class="row">
    <div class="col-md-7">
      <div class="card">
        <h5 class="card-header">Informations</h5>
        <div class="card-body">
          <img class="card-img-top" src="$img">
          <p></br><b>Description de l'objet : </b></br>$desc</p>
          <p><b>Tarif : </b>$tarif €</p>
          <p><b>URL Page marchande : </b>$url</p>
        </div>
      </div>
      <br>
    </div>

    <div class="col-md-5">
      <div class="card">
        <h5 class="card-header">Réservation</h5>
        <div class="card-body">
          $reservation
      </div>
    </div>

    </div>
  </div>
FIN;

    return $html;
  }

  /*-------------------------------------------------------------------------------------------*/
  /* MÉTHODE(S) POUR LA PARTIE ÉDITION */
  /*-------------------------------------------------------------------------------------------*/

  private function formulaireEdition(): string
  {
    // Les variables requises
    $url_postCreation = $this->container->router->pathFor('creer_item_post', ['tokenPublic' => $this->data['public'], 'tokenPrivate' => $this->data['private']]);

    $url_edition = $this->container->router->pathFor('edition_liste', [
      'tokenPublic' => $this->data['public'],
      'tokenPrivate' => $this->data['private'],
    ]);

    $nom = $this->data['item']->nom;
    $desc = $this->data['item']->descr;
    $img = $this->data['item']->img;
    $url = $this->data['item']->url;
    $tarif = $this->data['item']->tarif;

    // L'affichage
    $html = <<<FIN
    <div class="col-md-6 mx-auto">
      <h1 class="my-4 text-center">Édition d'un item</h1>
      <div class="card">
        <div class="card-body">
          <form role="form" method="POST" action="$url_postCreation">
            <div class="form-group my-3">
              <label>Titre</label>
              <input type="text" class="form-control" name="titre" placeholder="Titre" value="$nom" required>
            </div>
            <div class="form-group my-3">
              <label>Description</label>
              <textarea class="form-control" name="desc" placeholder="Description" required>$desc</textarea>
            </div>
            <div class="form-group my-3">
              <label>URL vers une page marché</label>
              <input type="text" class="form-control" name="titre" placeholder="URL Page produit" value="$url">
            </div>
            <div class="form-group my-3">
            <label>URL Image</label>
            <input type="text" class="form-control" name="titre" placeholder="URL Image" value="$img">
          </div>
          <div class="form-group my-3">
          <label>Tarif</label>
          <input type="text" class="form-control" name="titre" placeholder="Tarif"  value="$tarif" required>
        </div>
            <div class="form-group d-flex justify-content-around">
              <button type="submit" class="btn btn-success">Modifier</button>
              <a class="btn btn-outline-danger" href="$url_edition">Retour</a>
            </div>
          </form>
        </div>
      </div>
    </div>
FIN;

    return $html;
  }

  /*-------------------------------------------------------------------------------------------*/
  /* MÉTHODE(S) POUR LA PARTIE CRÉATION */
  /*-------------------------------------------------------------------------------------------*/

  private function formulaireCreation(): string
  {
    // Les variables requises
    $url_postCreation = $this->container->router->pathFor('creer_item_post', ['tokenPublic' => $this->data['public'], 'tokenPrivate' => $this->data['private']]);

    $url_edition = $this->container->router->pathFor('edition_liste', [
      'tokenPublic' => $this->data['public'],
      'tokenPrivate' => $this->data['private'],
    ]);

    // L'affichage
    $html = <<<FIN
    <div class="col-md-6 mx-auto">
      <h1 class="my-4 text-center">Création d'un item</h1>
      <div class="card">
        <div class="card-body">
          <form role="form" method="POST" action="$url_postCreation">
            <div class="form-group my-3">
              <label>Titre</label>
              <input type="text" class="form-control" name="titre" placeholder="Titre" required>
            </div>
            <div class="form-group my-3">
              <label>Description</label>
              <textarea class="form-control" name="desc" placeholder="Description" required></textarea>
            </div>
            <div class="form-group my-3">
              <label>URL vers une page marché</label>
              <input type="text" class="form-control" name="titre" placeholder="URL Page produit">
            </div>
            <div class="form-group my-3">
            <label>URL Image</label>
            <input type="text" class="form-control" name="titre" placeholder="URL Image">
          </div>
          <div class="form-group my-3">
          <label>Tarif</label>
          <input type="text" class="form-control" name="titre" placeholder="Tarif" required>
        </div>
            <div class="form-group d-flex justify-content-around">
              <button type="submit" class="btn btn-success">Créer</button>
              <a class="btn btn-outline-danger" href="$url_edition">Retour</a>
            </div>
          </form>
        </div>
      </div>
    </div>
FIN;


    return $html;
  }
}
