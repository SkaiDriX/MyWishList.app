<?php
namespace mywishlist\controls;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \mywishlist\models\Liste as Liste;

class ListeControleur {
	private $app;
	
	public function __construct($app) {
		$this->app = $app;
	}
	public function createListe(Request $rq, Response $rs, $args) {
		$rs->getBody()->write('Page de création d\'une liste') ;
		return $rs;
	}
	
	public function insertListe(Request $rq, Response $rs, $args) {
		// Insertion d'une liste
		return $rs;
	}	
	
	public function getListe(Request $rq, Response $rs, $args) {
		$rs->getBody()->write('Affichage de la liste') ;
		return $rs;
	}

	public function editListe(Request $rq, Response $rs, $args) {
		$rs->getBody()->write('<h1>Page d\'édition de la liste</h1>') ;

		$tokenPublic = $args['tokenPublic'];
		$tokenPrivate = $args['tokenPrivate'];

		$liste = Liste::select('titre', 'description', 'token_edit') -> where ('token', '=', $tokenPublic)->first();

		if(is_null($liste)){  
			// LISTE NON EXISTANTE
			return $rs->withRedirect($this->app->router->pathFor('accueil')); 
		} else{
			if($tokenPrivate == $liste->token_edit){
				// C BON
				echo '<b>' . $liste->titre . '</b><br>' . $liste->description . '<br><br>';
			}else{
				// TOKEN INVALIDE
				return $rs->withRedirect($this->app->router->pathFor('accueil')); 
			}
		}
			
		return $rs;
	}

	public function updateListe(Request $rq, Response $rs, $args) : Response {

		$tokenPublic = $args['tokenPublic'];
		$tokenPrivate = $args['tokenPrivate'];	

		$liste = Liste::select('titre', 'description', 'token_edit') -> where ('token', '=', $tokenPublic)->first();

		if(!is_null($liste) && ($tokenPrivate == $liste->token_edit)){  
			$post = $rq->getParsedBody() ;
			$titre = filter_var($post['titre'], FILTER_SANITIZE_STRING) ;
			$description = filter_var($post['description'] , FILTER_SANITIZE_STRING) ;

			$liste->titre = $titre;
			$liste->description = $description;
			$liste->save();
		}
		
		// NORMALEMENT REDIRECTION VERS LA PAGE DE LA LISTE MAIS PLUS TARD DU COUP
        return $rs->withRedirect($this->app->router->pathFor('accueil')); 
	}

	public function addMessage(Request $rq, Response $rs, $args) {
		// Ajout d'un message
		return $rs;
	}		
}

?>