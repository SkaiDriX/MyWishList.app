<?php
namespace mywishlist\controls;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \mywishlist\models\Liste as Liste;
use \mywishlist\views\VueListe as VueListe;
use \mywishlist\models\Users as Users;

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
		
		// Récupération des variables
		$post = $rq->getParsedBody() ;
		$titre = filter_var(trim($post['titre']), FILTER_SANITIZE_STRING);
		$description = filter_var(trim($post['desc']) , FILTER_SANITIZE_STRING);		
		$expiration = filter_var($post['date'] , FILTER_SANITIZE_STRING);

		// Vérifications des variables reçues
		if(strlen($titre) < 5) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Le titre de la liste doit faire au minimum 5 caractères");
			return $rs->withRedirect($this->app->router->pathFor('create_liste')); 
		} else if(strlen($description) < 5) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] La description de la liste doit faire au minimum 5 caractères");
			return $rs->withRedirect($this->app->router->pathFor('create_liste')); 
		} else if (new DateTime() > new DateTime($expiration)) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] La date d'expiration de la liste doit être supérieur à la date actuelle");
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
		$urlEditListe = $this->app->router->pathFor('edition_liste', [
            'tokenPublic' => $publicToken,
            'tokenPrivate' => $privateToken
		]);	
		
		$this->app->flash->addMessage('Ok', "[SUCCÈS] La liste a été créée");
       	return $rs->withRedirect($urlEditListe); 
	}	


	/**
	 * Méthode pour l'affichage d'une liste
	 */
	public function getListe(Request $rq, Response $rs, $args) {
		
		$tokenPublic = $args['tokenPublic'];
		
		// Vérification que la liste existe
		$liste = Liste::where('token', '=', $tokenPublic)->first();
		if(is_null($liste)){  
			$this->app->flash->addMessage('Alerte', "[ERREUR] La liste n'existe pas");
			return $rs->withRedirect($this->app->router->pathFor('accueil')); 
		} 

        // On regarde si l'utilisateur est connecté, dans le cas inverse alors on regarde le cookie identité
		$data['identite'] = "";
		$data['blockedIdentity'] = false;
        if (isset($_COOKIE['username'])) {
            $data['identite'] = unserialize($_COOKIE['username']);
        }
        
        if(isset($_SESSION['idUser']) && (!is_null(Users::where('id', '=', $_SESSION['idUser'])->first()))) {
                $user = Users::where('id', '=', $_SESSION['idUser'])->first();
                if(!is_null($user)) {
					$data['identite'] = $user->username;
					$data['blockedIdentity'] = true;
                }
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

		$data['messages'] = $liste->messages()->toArray();
		$data['liste'] = $liste;
		$data['items'] = $liste->items;
		$data['expired'] = $liste->isExpired();

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
		
		// Vérification que la liste existe
		$liste = Liste::where('token', '=', $tokenPublic)->first();
		if(is_null($liste)){  
			$this->app->flash->addMessage('Alerte', "[ERREUR] La liste n'existe pas");
			return $rs->withRedirect($this->app->router->pathFor('accueil')); 
		} 

        // Vérification que le token d'édition est correct
        if (!$liste->isEditable($tokenPrivate)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Le token de modification n'est pas valide");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        } 

        // Vérification que la liste n'est pas expirée
        if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible de modifier une liste expirée");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }
		
		$data['clePublique'] = $tokenPublic;
		$data['clePrive'] = $tokenPrivate;
		$data['items'] = $liste->items;
		$data['liste'] = $liste;
		$data['url'] = $_SERVER['SERVER_NAME'];

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

		// Vérification que la liste existe
		$liste = Liste::where('token', '=', $tokenPublic)->first();
		if(is_null($liste)){  
			$this->app->flash->addMessage('Alerte', "[ERREUR] La liste n'existe pas");
			return $rs->withRedirect($this->app->router->pathFor('accueil')); 
		} 

        // Vérification que le token d'édition est correct
        if (!$liste->isEditable($tokenPrivate)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Le token de modification n'est pas valide");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        } 

        // Vérification que la liste n'est pas expirée
        if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible de modifier une liste expirée");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

		// Récupération des variables
		$post = $rq->getParsedBody() ;
		$titre = filter_var($post['titre'], FILTER_SANITIZE_STRING) ;
		$description = filter_var($post['desc'] , FILTER_SANITIZE_STRING);
		$visibilite = filter_var($post['visibility'] , FILTER_SANITIZE_STRING);

		if(strlen($titre) < 5) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Le titre de la liste doit faire au minimum 5 caractères");
		} else if(strlen($description) < 5) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] La description de la liste doit faire au minimum 5 caractères");
		}  else {
			$liste->titre = $titre;
			$liste->description = $description;
			$liste->publique = ($visibilite == 'public') ? 1 : 0;
			$liste->save();
	
			$this->app->flash->addMessage('Ok', "[SUCCÈS] La liste a été modifiée");
		}
		
        return $rs->withRedirect($this->app->router->pathFor('edition_liste',[ 'tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate])); 
	}

	/**
	 * Méthode pour ajouter un message sur une liste
	 */
	public function addMessage(Request $rq, Response $rs, $args) {
		$tokenPublic = $args['tokenPublic'];

		// Vérification que la liste existe
		$liste = Liste::where('token', '=', $tokenPublic)->first();
		if(is_null($liste)){  
			$this->app->flash->addMessage('Alerte', "[ERREUR] La liste n'existe pas");
			return $rs->withRedirect($this->app->router->pathFor('accueil')); 
		} 

        // Vérification que la liste n'est pas expirée
        if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible d'ajouter un message sur une liste expirée");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

		// Récupération des variables
		$post = $rq->getParsedBody() ;
		$message = filter_var($post['message'], FILTER_SANITIZE_STRING) ;
		$identite = filter_var($post['identite'] , FILTER_SANITIZE_STRING);

		if(strlen($message) < 10) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Le message doit faire au minimum 10 caractères");
		} else if(strlen($identite) < 5) {
			$this->app->flash->addMessage('Alerte', "[ERREUR] Le pseudo doit faire au minimum 5 caractères.");
		}  else {
			$listeMessage = new ListeMessage();
			$listeMessage->liste_id = $liste->id;
			$listeMessage->nom = $identite;
			$listeMessage->message = $message;
			$listeMessage->date = new DateTime();
			$listeMessage->save();

			// Création cookie identité
			setcookie("username", serialize ($identite),time() + 60*60*24*365*10, "/" ) ;
	
			$this->app->flash->addMessage('Ok', "[SUCCÈS] Le message a été ajouté");
		}

		return $rs->withRedirect($this->app->router->pathFor('affichage_liste',[ 'tokenPublic' => $tokenPublic,])); 
	}		
}

?>