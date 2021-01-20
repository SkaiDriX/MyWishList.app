<?php
namespace mywishlist\controls;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \mywishlist\views\VueCompte as VueCompte;

use \mywishlist\models\Users as Users;

class AuthControleur {
	private $app;
	
	public function __construct($app) {
		$this->app = $app;
	}

	/**
	 * Page connexion
	 */
	public function login(Request $rq, Response $rs, $args) {
		if(isset($_SESSION['idUser'])) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Vous êtes déjà connecté");
			return $rs->withRedirect($this->app->router->pathFor('accueil'));
		}

		$vue = new VueCompte(null, $this->app ) ;
		$rs->getBody()->write($vue->render(0)) ;
		return $rs;
	}

	/**
	 * Page inscription
	 */
	public function register(Request $rq, Response $rs, $args) {
		if(isset($_SESSION['idUser'])) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Impossible de créer un compte en étant déjà connnecté");
			return $rs->withRedirect($this->app->router->pathFor('accueil'));
		}

		$vue = new VueCompte(null, $this->app ) ;
		$rs->getBody()->write($vue->render(1)) ;
		return $rs;
	}

	/**
	 * Méthode création utilisateur
	 */
	public function goRegister(Request $rq, Response $rs, $args) {

		if(isset($_SESSION['idUser'])) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Impossible de créer un compte en étant déjà connnecté");
			return $rs->withRedirect($this->app->router->pathFor('accueil'));
		}

        //Recuperation des informations du formulaire
        $post = $rq -> getParsedBody ();
        $identite = filter_var ( trim($post['username']), FILTER_SANITIZE_STRING );
		$password = filter_var ( trim($post['password']), FILTER_SANITIZE_STRING );
		
		// CONTRÔLE DES VALEURS
		if(strlen($identite) < 5 || strlen($identite) > 24) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Votre nom d'utilisateur doit faire entre 5 et 24 caractères");
			return $rs->withRedirect($this->app->router->pathFor('inscription'));
		} else if(strlen($password) < 5 || strlen($password) > 24) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Votre mot de passe doit faire entre 5 et 24 caractères");
			return $rs->withRedirect($this->app->router->pathFor('inscription'));
		}

		$user = new Users();
		$user->username = $identite;
		$user->password = password_hash($password, PASSWORD_DEFAULT );
		$user->save();

		$this->app->flash->addMessage('Ok', "[SUCCÈS] Le compte a bien été créé");
		return $rs->withRedirect($this->app->router->pathFor('accueil'));
	}

	/**
	 * Méthode de connexion
	 */
	public function goLogin(Request $rq, Response $rs, $args) {
		if(isset($_SESSION['idUser'])) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Vous êtes déjà connecté");
			return $rs->withRedirect($this->app->router->pathFor('accueil'));
		}
		
        //Recuperation des informations du formulaire
        $post = $rq -> getParsedBody ();
        $identite = filter_var ( trim($post['username']), FILTER_SANITIZE_STRING );
		$password = filter_var ( trim($post['password']), FILTER_SANITIZE_STRING );
		
		$user = Users::where('username', '=', $identite)->first();
        if (is_null($user)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Nom d'utilisateur introuvable");
            return $rs->withRedirect($this->app->router->pathFor('connexion'));
		} 

		if(!password_verify($password, $user->password)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Mot de passe incorrect");
            return $rs->withRedirect($this->app->router->pathFor('connexion'));
		}

		$_SESSION['idUser'] = $user->id;

		$this->app->flash->addMessage('Ok', "[SUCCÈS] Vous êtes maintenant connecté");
		return $rs->withRedirect($this->app->router->pathFor('accueil'));
	}

	/**
	 * Méthode pour la déconnexion
	 */
	public function logout(Request $rq, Response $rs, $args) {
		if(!isset($_SESSION['idUser'])) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Vous n'êtes pas connecté");
			return $rs->withRedirect($this->app->router->pathFor('accueil'));
		}
		
		unset($_SESSION['idUser']);

		$this->app->flash->addMessage('Ok', "[SUCCÈS] Vous vous êtes déconnecté");
		return $rs->withRedirect($this->app->router->pathFor('accueil'));
	}

	/**
	 * Méthode pour la suppression de l'utilisateur
	 */
	public function delete(Request $rq, Response $rs, $args) {
		if(!isset($_SESSION['idUser'])) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Vous n'êtes pas connecté");
			return $rs->withRedirect($this->app->router->pathFor('accueil'));
		}

		$user = Users::where('id', '=', $_SESSION['idUser'])->first();
		$user->delete();

		unset($_SESSION['idUser']);
		$this->app->flash->addMessage('Ok', "[SUCCÈS] Le compte a bien été supprimé");

		return $rs->withRedirect($this->app->router->pathFor('accueil'));
	}

	/**
	 * Méthode pour l'affichage du profil
	 */
	public function infos(Request $rq, Response $rs, $args) {
		if(!isset($_SESSION['idUser'])) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Vous n'êtes pas connecté");
			return $rs->withRedirect($this->app->router->pathFor('accueil'));
		}

		$user = Users::where('id', '=', $_SESSION['idUser'])->first();
        if (is_null($user)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Problème avec le compte !");
            return $this->logout;
		} 

		$data['user'] = $user;

		$vue = new VueCompte($data, $this->app) ;
		$rs->getBody()->write($vue->render(2)) ;
		return $rs;
	}

}
?>