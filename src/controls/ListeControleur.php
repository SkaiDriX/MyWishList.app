<?php
namespace mywishlist\controls;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \mywishlist\models\Liste as Liste;
use \mywishlist\views\VueListe as VueListe;

use DateTime;
use mywishlist\models\ListeMessage;

class ListeControleur {
	private $app;
	
	public function __construct($app) {
		$this->app = $app;
	}


	/**
	 * Méthode appelée lorsqu'on se rend sur la page de création d'une liste
	 */
	public function createListe(Request $rq, Response $rs, $args) {
		$vue = new VueListe(null, $this->app ) ;
		$rs->getBody()->write($vue->render(2));
		return $rs;
	}
	

	/**
	 * Méthode utilisée pour la création d'une liste
	 */
	public function insertListe(Request $rq, Response $rs, $args) {
		$post = $rq->getParsedBody() ;
		$titre = filter_var(trim($post['titre']), FILTER_SANITIZE_STRING);
		$description = filter_var(trim($post['desc']) , FILTER_SANITIZE_STRING);		
		$expiration = filter_var($post['date'] , FILTER_SANITIZE_STRING);

		// Vérifications des variables reçues
		if(strlen($titre) < 5) {
			$this->app->flash->addMessage('Alerte', 'Le titre doit au moins faire 5 caractères !');
			return $rs->withRedirect($this->app->router->pathFor('create_liste')); 
		} else if(strlen($description) < 5) {
			$this->app->flash->addMessage('Alerte', 'La description doit au moins faire 5 caractères !');
			return $rs->withRedirect($this->app->router->pathFor('create_liste')); 
		} else if (new DateTime() > new DateTime($expiration)) {
			$this->app->flash->addMessage('Alerte', 'La date doit être supérieur à celle actuelle !');
			return $rs->withRedirect($this->app->router->pathFor('create_liste')); 
		} 

		// Génération des tokens uniques
		do {
			$publicToken = bin2hex(random_bytes(6));
		} while(Liste::where ('token', '=', $publicToken)->exists());

		do {
			$privateToken = bin2hex(random_bytes(6));
		} while(Liste::where ('token', '=', $privateToken)->exists());

		// Création de la liste
		$liste = new Liste();
		$liste->user_id = -1;
		$liste->titre = $titre;
		$liste->description = $description;
		$liste->expiration = $expiration;
		$liste->token = $publicToken;
		$liste->token_edit = $privateToken;
		$liste->publique = 0;
		$liste->save();

		// Création du cookie
		if (!isset($_COOKIE['createdListe'])) {
			$cookie = $privateToken;
		} else {
			$cookie = unserialize ($_COOKIE['createdListe']).';'.$privateToken;
		}
		setcookie("createdListe", serialize ($cookie),time() + 60*60*24*365*10, "/" ) ;

		// Redirection vers la page d'édition de la liste
		$url_editListe = $this->app->router->pathFor('edition_liste', [
            'tokenPublic' => $publicToken,
            'tokenPrivate' => $privateToken
		]);	
		
		$this->app->flash->addMessage('Ok', 'La liste a été créé !');
       	return $rs->withRedirect($url_editListe); 
	}	


	/**
	 * Méthode pour l'affichage d'une liste
	 */
	public function getListe(Request $rq, Response $rs, $args) {
		
		$tokenPublic = $args['tokenPublic'];
		$liste = Liste::where('token', '=', $tokenPublic)->first();

		if(is_null($liste)){  
			$this->app->flash->addMessage('Alerte', 'La liste n\'existe pas !');
			return $rs->withRedirect($this->app->router->pathFor('accueil')); 
		} 

		$data['messages'] = $liste->messages()->toArray();
		$data['liste'] = $liste;

		// On regarde si l'utilisateur a déjà un pseudo
		$data['identite'] = "";
		if (isset($_COOKIE['username'])) {
			$data['identite'] = unserialize($_COOKIE['username']);
		}
		
		// On regarde si l'utilisateur est le créateur de la liste
		$data['isOwner'] = 0;

		if (isset($_COOKIE['createdListe'])) {
			$listeTable = unserialize($_COOKIE['createdListe']);
			$listeTable = explode(";", $listeTable);
			if (in_array($liste->token_edit, $listeTable)) {
				$data['isOwner'] = 1;
			}
		}

		$vue = new VueListe($data, $this->app ) ;
		$rs->getBody()->write($vue->render(3)) ;
		return $rs;
	}


	/**
	 * Méthode appelée pour avoir la page de la modification de la liste
	 */
	public function editListe(Request $rq, Response $rs, $args) {
		$tokenPublic = $args['tokenPublic'];
		$tokenPrivate = $args['tokenPrivate'];
		
		$liste = Liste::where('token', '=', $tokenPublic)->first();

		if(is_null($liste)){  
			$this->app->flash->addMessage('Alerte', 'La liste n\'existe pas !');
			return $rs->withRedirect($this->app->router->pathFor('accueil')); 
		} 
		else if (!$liste->isEditable($tokenPrivate)) 
		{
			$this->app->flash->addMessage('Alerte', 'Le token de modification est invalide !');
			return $rs->withRedirect( $this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic])); 
		} 
		else if ($liste->isExpired()) 
		{
			$this->app->flash->addMessage('Alerte', 'Il n\'est pas possible de modifier une liste expirée !');
			return $rs->withRedirect( $this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic])); 
		}
		
		$data['clePublique'] = $tokenPublic;
		$data['clePrive'] = $tokenPrivate;
		$data['liste'] = $liste;
		$data['url'] = $rq->getUri()->getBaseUrl();

		$vue = new VueListe($data, $this->app ) ;
		$rs->getBody()->write($vue->render(1)) ;
		return $rs;
	}


	/**
	 * Méthode qui s'occupe de modifier la liste
	 */
	public function updateListe(Request $rq, Response $rs, $args) : Response {

		$tokenPublic = $args['tokenPublic'];
		$tokenPrivate = $args['tokenPrivate'];	

		$liste = Liste::where ('token', '=', $tokenPublic)->first();

		if(is_null($liste)){  
			$this->app->flash->addMessage('Alerte', 'La liste n\'existe pas !');
			return $rs->withRedirect($this->app->router->pathFor('accueil')); 
		} 
		else if (!$liste->isEditable($tokenPrivate)) 
		{
			$this->app->flash->addMessage('Alerte', 'Le token de modification est invalide !');
			return $rs->withRedirect( $this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic])); 
		} 
		else if ($liste->isExpired()) 
		{
			$this->app->flash->addMessage('Alerte', 'Il n\'est pas possible de modifier une liste expirée !');
			return $rs->withRedirect( $this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic])); 
		}

		$post = $rq->getParsedBody() ;
		$titre = filter_var($post['titre'], FILTER_SANITIZE_STRING) ;
		$description = filter_var($post['desc'] , FILTER_SANITIZE_STRING);
		$visibilite = filter_var($post['visibility'] , FILTER_SANITIZE_STRING);

		if(strlen($titre) < 5) {
			$this->app->flash->addMessage('Alerte', 'Le titre doit au moins faire 5 caractères !');
		} else if(strlen($description) < 5) {
			$this->app->flash->addMessage('Alerte', 'La description doit au moins faire 5 caractères !');
		}  else {
			$liste->titre = $titre;
			$liste->description = $description;
			$liste->publique = ($visibilite == 'public') ? 1 : 0;
			$liste->save();
	
			$this->app->flash->addMessage('Ok', 'La liste a été modifiée !');
		}
		
        return $rs->withRedirect($this->app->router->pathFor('edition_liste',[ 'tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate])); 
	}

	public function addMessage(Request $rq, Response $rs, $args) {
		$tokenPublic = $args['tokenPublic'];

		$liste = Liste::where ('token', '=', $tokenPublic)->first();

		if(is_null($liste)){  
			$this->app->flash->addMessage('Alerte', 'La liste n\'existe pas !');
			return $rs->withRedirect($this->app->router->pathFor('accueil')); 
		} 

		$post = $rq->getParsedBody() ;
		$message = filter_var($post['message'], FILTER_SANITIZE_STRING) ;
		$identite = filter_var($post['identite'] , FILTER_SANITIZE_STRING);

		if(strlen($message) < 10) {
			$this->app->flash->addMessage('Alerte', 'Le message doit au moins faire 10 caractères !');
		} else if(strlen($identite) < 5) {
			$this->app->flash->addMessage('Alerte', 'Votre pseudo doit faire au moins 5 caractères !');
		}  else {
			$listeMessage = new ListeMessage();
			$listeMessage->liste_id = $liste->id;
			$listeMessage->nom = $identite;
			$listeMessage->message = $message;
			$listeMessage->date = new DateTime();
			$listeMessage->save();

			// Création cookie identité
			setcookie("username", serialize ($identite),time() + 60*60*24*365*10, "/" ) ;
	
			$this->app->flash->addMessage('Ok', 'Le message a été ajouté.');
		}

		return $rs->withRedirect($this->app->router->pathFor('affichage_liste',[ 'tokenPublic' => $tokenPublic,])); 
	}		
}

?>