<?php
namespace mywishlist\controls;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \mywishlist\models\Liste as Liste;

use \mywishlist\views\VueListe as VueListe;

use DateTime;

class ListeControleur {
	private $app;
	
	public function __construct($app) {
		$this->app = $app;
	}

	public function createListe(Request $rq, Response $rs, $args) {
		$vue = new VueListe(null, $this->app ) ;
		$rs->getBody()->write($vue->render(2));
		return $rs;
	}
	
	public function insertListe(Request $rq, Response $rs, $args) {
		$post = $rq->getParsedBody() ;
		$titre = filter_var(trim($post['titre']), FILTER_SANITIZE_STRING);
		$description = filter_var(trim($post['desc']) , FILTER_SANITIZE_STRING);		
		$expiration = filter_var($post['date'] , FILTER_SANITIZE_STRING);

		// VÉRIFICATIONS
		if(strlen($titre) < 4) {
			// Message erreur titre
			return $rs->withRedirect($this->app->router->pathFor('create_liste')); 
		} else if(strlen($description) < 4) {
			// Message erreur descritpion
			return $rs->withRedirect($this->app->router->pathFor('create_liste')); 
		} else if (new DateTime() > new DateTime($expiration)) {
			// Message erreur date
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
		//ICI

		// Redirection vers la page d'édition de la liste
		$url_editListe = $this->app->router->pathFor('edition_liste', [
            'tokenPublic' => $publicToken,
            'tokenPrivate' => $privateToken
		]);	
		
       	return $rs->withRedirect($url_editListe); 
	}	
	
	public function getListe(Request $rq, Response $rs, $args) {
		$tokenPublic = $args['tokenPublic'];

		$liste = Liste::where('token', '=', $tokenPublic)->first();

		if(is_null($liste)){  
			// LISTE NON EXISTANTE
			return $rs->withRedirect($this->app->router->pathFor('accueil')); 
		} 
		
		$data['liste'] = $liste;
		$data['isOwner'] = 0; // ICI A MODIFIER EN FONCTION DU COOKIE

		$vue = new VueListe($data, $this->app ) ;
		$rs->getBody()->write($vue->render(3)) ;
		return $rs;
	}

	public function editListe(Request $rq, Response $rs, $args) {
		$tokenPublic = $args['tokenPublic'];
		$tokenPrivate = $args['tokenPrivate'];

		$liste = Liste::select('titre', 'description', 'token_edit') -> where ('token', '=', $tokenPublic)->first();

		if(is_null($liste)){  
			// LISTE NON EXISTANTE
			return $rs->withRedirect($this->app->router->pathFor('accueil')); 
		} else if ($tokenPrivate != $liste->token_edit) {
			// TOKEN INVALIDE
			return $rs->withRedirect($this->app->router->pathFor('accueil')); 
		}
		
		$data['clePublique'] = $tokenPublic;
		$data['clePrive'] = $tokenPrivate;
		$data['liste'] = $liste;

		$vue = new VueListe($data, $this->app ) ;
		$rs->getBody()->write($vue->render(1)) ;
		return $rs;
	}

	public function updateListe(Request $rq, Response $rs, $args) : Response {

		$tokenPublic = $args['tokenPublic'];
		$tokenPrivate = $args['tokenPrivate'];	

		$liste = Liste::where ('token', '=', $tokenPublic)->first();

		if(!is_null($liste) && ($tokenPrivate == $liste->token_edit)){  
			$post = $rq->getParsedBody() ;
			$titre = filter_var($post['titre'], FILTER_SANITIZE_STRING) ;
			$description = filter_var($post['desc'] , FILTER_SANITIZE_STRING);

			if(strlen($titre) < 4) {
				// Message erreur titre
				return $rs->withRedirect($this->app->router->pathFor('create_liste')); 
			} else if(strlen($description) < 4) {
				// Message erreur descritpion
				return $rs->withRedirect($this->app->router->pathFor('create_liste')); 
			} 

			$liste->titre = $titre;
			$liste->description = $description;
			$liste->save();
		} else {
			// MESSAGE ERREUR LISTE 
		}
		
		// NORMALEMENT REDIRECTION VERS LA PAGE DE LA LISTE MAIS PLUS TARD DU COUP
        return $rs->withRedirect($this->app->router->pathFor('accueil')); 
	}

	/*public function addMessage(Request $rq, Response $rs, $args) {
		// Ajout d'un message
		return $rs;
	}	*/	
}

?>