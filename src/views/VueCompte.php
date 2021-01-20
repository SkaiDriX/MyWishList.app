<?php

namespace mywishlist\views;

class VueCompte extends Vue
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
      case 0:
        $this->content = $this->connect();
        $this->titre_page = "Connexion";
        break;
      case 1:
        $this->content = $this->register();
        $this->titre_page = "Inscription";
        break;
      case 2:
        $this->content = $this->monCompte();
        $this->titre_page = "Mon compte";
        break;        
    }

    return parent::render();
  }

  /*-------------------------------------------------------------------------------------------*/
  /* MÉTHODE(S) POUR LA PARTIE CONNEXION */
  /*-------------------------------------------------------------------------------------------*/
  private function connect(): string
  {
    // Les variables requises
    $urlRegister = $this->container->router->pathFor('inscription');
    $urlPostLogin = $this->container->router->pathFor('connexion_post');

    // L'affichage
    $html = <<<FIN
    <div class="col-md-6 mx-auto">
      <h1 class="my-4 text-center">Connexion</h1>
      <div class="card">
        <div class="card-body">
          <form role="form" method="POST" action="$urlPostLogin">
            <div class="form-group my-3">
              <label>Nom d'utilisateur</label>
              <input type="text" class="form-control" name="username" placeholder="Nom d'utilisateur" required>
            </div>
            <div class="form-group my-3">
              <label>Mot de passe</label>
              <input type="password" class="form-control" name="password" placeholder="Mot de passe" required>
            </div>
            <div class="form-group d-flex justify-content-around">
              <button type="submit" class="btn btn-success">Connexion</button>
              <a class="btn btn-outline-danger" href="$urlRegister">Inscription</a>
            </div>
          </form>
        </div>
      </div>
    </div>
FIN;

    return $html;
  }

  /*-------------------------------------------------------------------------------------------*/
  /* MÉTHODE(S) POUR LA PARTIE INSCRIPTION  */
  /*-------------------------------------------------------------------------------------------*/

  private function register(): string
  {
    // Les variables requises
    $urlPostRegister = $this->container->router->pathFor('inscription_post');
    $urlAccueil = $this->container->router->pathFor('accueil');

    // L'affichage
    $html = <<<FIN
    <div class="col-md-6 mx-auto">
      <h1 class="my-4 text-center">Création du compte</h1>
      <div class="card">
        <div class="card-body">
          <form role="form" method="POST" action="$urlPostRegister">
            <div class="form-group my-3">
              <label>Nom d'utilisateur</label>
              <input type="text" class="form-control" name="username" placeholder="Nom d'utilisateur" required>
            </div>
            <div class="form-group my-3">
              <label>Mot de passe</label>
              <input type="password" class="form-control" name="password" placeholder="Mot de passe" required>
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
  /* MÉTHODE(S) POUR LA PARTIE 'MON COMPTE' */
  /*-------------------------------------------------------------------------------------------*/
  private function monCompte(): string
  {
    // Les variables requises
    $user = $this->data['user'];
    $urlLogout = $this->container->router->pathFor('deconnexion');
    $urlDelete = $this->container->router->pathFor('delete_account');

    // L'affichage
    $html = <<<FIN
    <div class="col-md-6 mx-auto">
      <h1 class="my-4 text-center">Mon compte</h1>
      <div class="card">
        <div class="card-body text-center">
            <b>Nom d'utilisateur :</b> $user->username</br></br>
            <div class="d-flex justify-content-between">
              <a class="btn btn-outline-secondary" href="$urlLogout">Déconnexion</a>
              <a class="btn btn-danger" href="$urlDelete">Suppression du compte</a>
            </div>
        </div>
      </div>
    </div>
FIN;

    return $html;
  }

}
