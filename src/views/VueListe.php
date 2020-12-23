<?php

namespace mywishlist\views;

class VueListe extends Vue
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

  /*-------------------------------------------------------------------------------------------*/
  /* MÉTHODE(S) POUR LA PARTIE ÉDITION */
  /*-------------------------------------------------------------------------------------------*/

  private function formulaireEdition(): string
  {
    // Les variables requises
    $nomliste = $this->data['liste']->titre;
    $descliste = $this->data['liste']->description;
    $visibility = $this->data['liste']->publique;
    $edit = $this->data['liste']->token_edit;
    $token = $this->data['liste']->token;

    $url_liste = $this->container->router->pathFor('affichage_liste', [
      'tokenPublic' => $token,
    ]);

    $url_postEdition = $this->container->router->pathFor('edition_liste_post', [
      'tokenPublic' => $token,
      'tokenPrivate' => $edit,
    ]);

    $url_edition = $this->container->router->pathFor('edition_liste', [
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

    // L'affichage
    $html = <<<FIN
<div class="my-4 d-flex justify-content-center align-self-center flex-column text-center">
  <h1>$nomliste - Édition</h1>
  <div class="align-self-center mt-2">
    <a href="$url_liste" class="btn btn-outline-secondary">Retour</a>
  </div>
</div>

<div class="row">
  <div class="col-md-7 mb-4">
    <div class="card">
      <h5 class="card-header">Modification</h5>
      <div class="card-body">
        <form role="form" method="POST" action="$url_postEdition">
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
  </div>
  
  <div class="col-md-5">
    <div class="card text-white bg-danger">
      <h5 class="card-header">Token de modification</h5>
      <div class="card-body">
        <p style="text-align:justify;">Veuillez sauvegarder ce token de modification, sans lui vous ne pourrez plus modifier votre liste !<p>
        <li class="list-group-item" style="background-color: #00000059;">
          <h5 class="text-center">$edit</h5>
        </li>
        <br>
        <p>Votre lien de modification est donc :<p>
        <li class="list-group-item text-center" style="background-color: #00000059;">
          <span>{$this->data['url']}$url_edition</span>
        </li>
      </div>
    </div>
    <div class="card my-4 text-white bg-success">
      <h5 class="card-header">Lien de partage</h5>
      <div class="card-body">
      <p style="text-align:justify;">Vous pouvez utiliser le lien suivant pour partager votre liste à vos amis :<p>
      <li class="list-group-item text-center" style="background-color: #00000059;">
        <span>{$this->data['url']}$url_liste</span>
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

  private function formulaireCreation(): string
  {
    // Les variables requises
    $url_postCreation = $this->container->router->pathFor('create_liste_post');
    $url_accueil = $this->container->router->pathFor('accueil');

    // L'affichage
    $html = <<<FIN
    <div class="col-md-6 mx-auto">
      <h1 class="my-4 text-center">Création d'une liste</h1>
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
              <label>Date d'expiration</label>
              <div class="form-group">
                <input type="date" class="form-control" name="date" required>
              </div>
            </div>

            <div class="form-group d-flex justify-content-around">
              <button type="submit" class="btn btn-success">Créer</button>
              <a class="btn btn-outline-danger" href="$url_accueil">Retour</a>
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

  private function voirListe(): string
  {
    // Les variables requises
    $liste = $this->data['liste'];

    $titre = $liste->titre;
    $description = $liste->description;
    $expirationDate = $liste->expiration;
    $auteur = "Anonyme";
    $isCreateur = $this->data['isOwner'];

    // Le bouton de modification de liste
    $btn = "";
    if ($isCreateur == 1) {
      $url_edition = $this->container->router->pathFor('edition_liste', [
        'tokenPublic' => $liste->token,
        'tokenPrivate' => $liste->token_edit,
      ]);

      $btn = '      
        <div class="align-self-center mt-2">
          <a href="' . $url_edition . '" class="btn btn-outline-danger">Modifier la liste</a>
        </div>';
    }

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
          <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge rounded-pill bg-success">Non réservé</span>
                <span>Titre de l'objet</span>
              </div>
              <div>
                <button class="btn btn-secondary">Voir</button>
              </div>
            </div>
          </li>
          <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge rounded-pill bg-danger">Réservé</span>
                <span>Titre de l'objet 2</span>
              </div>
              <div>
                <button class="btn btn-secondary">Voir</button>
              </div>
            </div>
          </li>
          <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge rounded-pill bg-success">Non réservé</span>
                <span>Titre de l'objet</span>
              </div>
              <div>
                <button class="btn btn-secondary">Voir</button>
              </div>
            </div>
          </li>
          <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge rounded-pill bg-danger">Réservé</span>
                <span>Titre de l'objet 2</span>
              </div>
              <div>
                <button class="btn btn-secondary">Voir</button>
              </div>
            </div>
          </li>
          <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge rounded-pill bg-success">Non réservé</span>
                <span>Titre de l'objet</span>
              </div>
              <div>
                <button class="btn btn-secondary">Voir</button>
              </div>
            </div>
          </li>
          <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge rounded-pill bg-danger">Réservé</span>
                <span>Titre de l'objet 2</span>
              </div>
              <div>
                <button class="btn btn-secondary">Voir</button>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="card">
      <h5 class="card-header">Messages</h5>
      <div class="card-body" style="overflow-y: scroll; max-height: 350px;">
        <ul class="list-group">
          <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <span style="font-weight:bold;">Marie</span>
              <small class="text-secondary" style="font-size:0.7em;">Hier</small>
            </div>
            <div style="font-size:0.7em;">AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA</div>
          </li>
          <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <span style="font-weight:bold;">Marie</span>
              <small class="text-secondary" style="font-size:0.7em;">Hier</small>
            </div>
            <div style="font-size:0.7em;">AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA</div>
          </li>
          <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <span style="font-weight:bold;">Marie</span>
              <small class="text-secondary" style="font-size:0.7em;">Hier</small>
            </div>
            <div style="font-size:0.7em;">AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA</div>
          </li>
          <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <span style="font-weight:bold;">Marie</span>
              <small class="text-secondary" style="font-size:0.7em;">Hier</small>
            </div>
            <div style="font-size:0.7em;">AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA</div>
          </li>
          <li class="list-group-item">
          <div class="d-flex justify-content-between align-items-center">
            <span style="font-weight:bold;">Marie</span>
            <small class="text-secondary" style="font-size:0.7em;">Hier</small>
          </div>
          <div style="font-size:0.7em;">AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA</div>
        </li>
        <li class="list-group-item">
          <div class="d-flex justify-content-between align-items-center">
            <span style="font-weight:bold;">Marie</span>
            <small class="text-secondary" style="font-size:0.7em;">Hier</small>
          </div>
          <div style="font-size:0.7em;">AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA</div>
        </li>
      </ul>
    </div>
  </div>

  <div class="card my-4">
    <h5 class="card-header">Ajout d'un message</h5>
    <div class="card-body">
      <form method="POST" action="#">
        <textarea rows="4" cols="55" class="form-control" name="message" ></textarea>
        <div class="input-group mb-3 mt-4">
          <span class="input-group-text">Identité</span>
          <input type="text" class="form-control">
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
