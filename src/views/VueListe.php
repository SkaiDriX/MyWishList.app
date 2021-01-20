<?php

namespace mywishlist\views;

class VueListe extends Vue
{

  public function __construct($data, $container)
  {
    parent::__construct($data, $container);
  }

  /**
   * Méthode de rendu
   */
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
        $this->titre_page = "Affichage d'une liste";
        break;
    }

    return parent::render();
  }

  /*-------------------------------------------------------------------------------------------*/
  /* MÉTHODE(S) POUR LA PARTIE ÉDITION */
  /*-------------------------------------------------------------------------------------------*/

  /**
   * Méthode pour récuperer la liste des items
   */
  private function getItems()
  {
    $html = "";

    foreach ($this->data['items'] as $i) {
      $titre = $i->nom;

      $urlEdit = $this->container->router->pathFor('edition_item', [
        'tokenPublic' => $this->data['liste']->token,
        'tokenPrivate' => $this->data['liste']->token_edit,
        'idItem' => $i->id
      ]);

      $urlDelete = $this->container->router->pathFor('suppression_item', [
        'tokenPublic' => $this->data['liste']->token,
        'tokenPrivate' => $this->data['liste']->token_edit,
        'idItem' => $i->id
      ]);

      // On regarde si l'item est réservé
      if ($i->isReserved()) {
        $etat = '<span class="badge rounded-pill bg-danger">Réservé</span>';
        $btn = '';
      } else {
        $etat = '<span class="badge rounded-pill bg-success">Non réservé</span>';
        $btn = '<div>
        <a class="btn btn-success" href="' . $urlEdit . '">Éditer</a>
        <a class="btn btn-danger" href="' . $urlDelete . '">Supprimer</a>
        </div>';
      }

      // Code HTML
      $html = <<<FIN
      $html 
      <li class="list-group-item">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          $etat
          <span>$titre</span>
        </div>
        $btn
      </div>
    </li>
FIN;
    }

    return $html;
  }

  /**
   * Méthode d'affichage pour la page d'édition de la liste
   */
  private function formulaireEdition(): string
  {
    // Les variables requises
    $nomliste = $this->data['liste']->titre;
    $descliste = $this->data['liste']->description;
    $visibility = $this->data['liste']->publique;
    $edit = $this->data['liste']->token_edit;
    $token = $this->data['liste']->token;

    $urlListe = $this->container->router->pathFor('affichage_liste', [
      'tokenPublic' => $token,
    ]);

    $urlPostEdition = $this->container->router->pathFor('edition_liste_post', [
      'tokenPublic' => $token,
      'tokenPrivate' => $edit,
    ]);

    $urlEdition = $this->container->router->pathFor('edition_liste', [
      'tokenPublic' => $token,
      'tokenPrivate' => $edit,
    ]);

    $urlAddItem = $this->container->router->pathFor('creer_item', [
      'tokenPublic' => $token,
      'tokenPrivate' => $edit,
    ]);


    $statusPublique = "";
    $statusPrive = "";

    if ($visibility == 0) {
      $statusPrive = "checked";
    } else {
      $statusPublique = "checked";
    }

    $items = $this->getItems();

    // L'affichage
    $html = <<<FIN
<div class="my-4 d-flex justify-content-center align-self-center flex-column text-center">
  <h1>$nomliste - Édition</h1>
  <div class="align-self-center mt-2">
    <a href="$urlListe" class="btn btn-outline-secondary">Retour</a>
  </div>
</div>

<div class="row">
  <div class="col-md-7 mb-4">
    <div class="card">
      <h5 class="card-header">Modification</h5>
      <div class="card-body">
        <form role="form" method="POST" action="$urlPostEdition">
          <div class="form-group my-2">
            <label>Titre</label>
            <input type="text" class="form-control" name="titre" placeholder="Titre" value="$nomliste" required="">
          </div>
          <div class="form-group my-2">
            <label>Description</label>
            <textarea class="form-control" name="desc" placeholder="Description" required="">$descliste</textarea>
          </div>

          <div class="d-flex justify-content-around">
            <div class="form-check form-check-inline my-2">
              <input class="form-check-input" type="radio" name="visibility" id="inlineRadio1" value="public" $statusPublique>
              <label class="form-check-label" for="inlineRadio1">Public</label>
            </div>

            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="visibility" id="inlineRadio2" value="private" $statusPrive>
              <label class="form-check-label" for="inlineRadio2">Privée</label>
            </div>
          </div>

          <div class="form-group d-flex justify-content-around">
            <button type="submit" class="btn btn-primary">Modifier</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card my-4">
      <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
      <h5 style="margin-bottom: 0;">Objets</h5>
      <a class="btn btn-success" style="font-size: 0.8em;" href="$urlAddItem">Ajouter</a>
      </div>
      <div class="card-body" style="overflow-y:scroll; max-height:400px;">
        <ul class="list-group">
          $items
        </ul>
      </div>
    </div>
  </div>
  
  <div class="col-md-5">
    <div class="card text-white bg-danger">
      <h5 class="card-header">Tokens</h5>
      <div class="card-body">
      <p style="text-align:justify;">Voici le token publique de votre liste, veuillez le garder car vous en aurez besoin en complétement du token de modification !<p>      
      <li class="list-group-item" style="background-color: #00000059;">
        <h5 class="text-center">$token</h5>
      </li>
      </br>
        <p style="text-align:justify;">Veuillez sauvegarder ce token de modification, sans lui vous ne pourrez plus modifier votre liste !<p>      
        <li class="list-group-item" style="background-color: #00000059;">
          <h5 class="text-center">$edit</h5>
        </li>
        <br>
        <p>Votre lien de modification est donc :<p>
        <li class="list-group-item text-center" style="background-color: #00000059;">
          <span>{$this->data['url']}$urlEdition</span>
        </li>
      </div>
    </div>
    <div class="card my-4 text-white bg-success">
      <h5 class="card-header">Lien de partage</h5>
      <div class="card-body">
      <p style="text-align:justify;">Vous pouvez utiliser le lien suivant pour partager votre liste à vos amis :<p>
      <li class="list-group-item text-center" style="background-color: #00000059;">
        <span>{$this->data['url']}$urlListe</span>
      </li>
      </div>
    </div>
  </div>
</div>
FIN;

    return $html;
  }

  /*-------------------------------------------------------------------------------------------*/
  /* MÉTHODE(S) POUR LA PARTIE CRÉATION */
  /*-------------------------------------------------------------------------------------------*/

  /**
   * Méthode pour la vue du formulaire de création d'une liste
   */
  private function formulaireCreation(): string
  {
    // Les variables requises
    $urlPostCreation = $this->container->router->pathFor('create_liste_post');
    $urlAccueil = $this->container->router->pathFor('accueil');

    // L'affichage
    $html = <<<FIN
    <div class="col-md-6 mx-auto">
      <h1 class="my-4 text-center">Création d'une liste</h1>
      <div class="card">
        <div class="card-body">
          <form role="form" method="POST" action="$urlPostCreation">
            <div class="form-group my-3">
              <label>Titre</label>
              <input type="text" class="form-control" name="titre" placeholder="Titre" required>
            </div>
            <div class="form-group my-3">
              <label>Description</label>
              <textarea class="form-control" name="desc" placeholder="Description" required></textarea>
            </div>

            <div class="form-group my-3">
              <label>Date d'expiration</label>
              <div class="form-group">
                <input type="date" class="form-control" name="date" required>
              </div>
            </div>

            <div class="form-group d-flex justify-content-around">
              <button type="submit" class="btn btn-success">Créer</button>
              <a class="btn btn-outline-danger" href="$urlAccueil">Retour</a>
            </div>
          </form>
        </div>
      </div>
    </div>
FIN;


    return $html;
  }

  /*-------------------------------------------------------------------------------------------*/
  /* MÉTHODE(S) POUR LA PARTIE AFFICHAGE DE LISTE */
  /*-------------------------------------------------------------------------------------------*/

  /**
   * Méthode pour l'affichage des items (côté liste)
   */
  private function affichageItems()
  {
    $html = "";

    foreach ($this->data['items'] as $i) {
      $titre = $i->nom;

      $urlItem = $this->container->router->pathFor('affichage_item', [
        'tokenPublic' => $this->data['liste']->token,
        'idItem' => $i->id
      ]);

      if ($i->isReserved()) {
        $etat = '<span class="badge rounded-pill bg-danger">Réservé</span>';
      } else {
        $etat = '<span class="badge rounded-pill bg-success">Non réservé</span>';
      }

      $html = <<<FIN
      $html 
      <li class="list-group-item">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          $etat
          <span>$titre</span>
        </div>
        <div>
			<a class="btn btn-primary" href="$urlItem">Voir</a>
        </div>
      </div>
    </li>
FIN;
    }

    return $html;
  }

  /**
   * Méthode pour récupérer les messages
   */
  private function getMessages(): string
  {
    $html = "";

    foreach ($this->data['messages'] as $m) {
      $date = date('d F Y', strtotime($m['date']));
      $message = $m['message'];
      $nom = $m['nom'];

      $content = <<<FIN
      <li class="list-group-item">
      <div class="d-flex justify-content-between align-items-center">
        <span style="font-weight:bold;">$nom</span>
        <small class="text-secondary" style="font-size:0.7em;">$date</small>
      </div>
      <div style="font-size:0.7em;">$message</div>
    </li>      
FIN;

      $html .= $content;
    }

    return $html;
  }

  /**
   * Méthode pour l'affichage de la liste
   */
  private function voirListe(): string
  {
    // Les variables requises
    $liste = $this->data['liste'];

    $titre = $liste->titre;
    $description = $liste->description;
    $auteur = "Anonyme";
    $isCreateur = $this->data['isOwner'];
    $identite = $this->data['identite'];

    $urlMessage = $this->container->router->pathFor('add_message_post', [
      'tokenPublic' => $liste->token
    ]);

    // On regarde si la liste est expirée
    if ($this->data['expired']) {
      $expirationDate = "Expirée";
    } else {
      $expirationDate = $liste->expiration;
    }

    // Affichage du bouton "modifier" si on est le créateur
    $btn = "";
    if ($isCreateur == 1) {
      $urlEdition = $this->container->router->pathFor('edition_liste', [
        'tokenPublic' => $liste->token,
        'tokenPrivate' => $liste->token_edit,
      ]);

      $btn = '      
        <div class="align-self-center mt-2">
          <a href="' . $urlEdition . '" class="btn btn-outline-danger">Modifier la liste</a>
        </div>';
    }

    // Affichage des messages si on est le créateur ou pas
    if ($isCreateur == 1 && !$this->data['expired']) {
      $listeMessages = "En tant que créateur de la liste, vous ne pouvez pas voir les messages tant qu'elle n'est pas expirée !";
    } else {
      $listeMessages = $this->getMessages();
    }

    // Récupération des items
    $items = $this->affichageItems();

    // L'affichage
    $html = <<<FIN

          
<div class="my-4 d-flex justify-content-center align-self-center flex-column text-center">
  <h1>$titre</h1>
  $btn        
</div>

<div class="row">
  <div class="col-md-7">
    <div class="card">
      <h5 class="card-header">Description</h5>
      <div class="card-body">
        <p>$description</p>
        <center><i style="font-size:0.8em;"><u>Date d'expiration</u> : $expirationDate ~ <u>Auteur</u> : $auteur</i></center>
      </div>
    </div>
    <div class="card my-4">
      <h5 class="card-header">Objets</h5>
      <div class="card-body" style="overflow-y:scroll; max-height:400px;">
        <ul class="list-group">
          $items
        </ul>
      </div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="card">
      <h5 class="card-header">Messages</h5>
      <div class="card-body" style="overflow-y: scroll; max-height: 350px;">
        <ul class="list-group">
          $listeMessages
        </ul>
    </div>
  </div>

  <div class="card my-4">
    <h5 class="card-header">Ajout d'un message</h5>
    <div class="card-body">
      <form method="POST" action="$urlMessage">
        <textarea rows="4" cols="55" class="form-control" name="message" ></textarea>
        <div class="input-group mb-3 mt-4">
          <span class="input-group-text">Pseudo</span>
          <input type="text" name="identite" class="form-control" value="$identite">
        </div>
        <div class="d-flex justify-content-center">
          <button class="btn btn-success">Ajouter le message</button>
        </div>
      </form>
    </div>
  </div>
  </div>
</div>
FIN;

    return $html;
  }
}
