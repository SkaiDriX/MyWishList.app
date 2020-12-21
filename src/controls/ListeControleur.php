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

		$query = Liste::select('id', 'titre', 'description', 'token', 'token_edit') -> where ('token', '=', $tokenPublic);
		$res = $query->get();

		if($res->isEmpty()){
			$this->app->router->pathFor('accueil');
		}else{
			foreach ($res as $entree) {
				if($tokenPrivate == $entree->token_edit){
					echo '<b>' . $entree->titre . '</b><br>' . $entree->description . '<br><br>';
				}else{
					echo '<h2>Token d\'édition incorrect.</h2>';
				}
			}
			
		}
		
		
		return $rs;
	}		

	public function updateListe(Request $rq, Response $rs, $args) : Response {
		$post = $rq->getParsedBody() ;
        $titre = filter_var($post['titre'], FILTER_SANITIZE_STRING) ;
        $description = filter_var($post['description'] , FILTER_SANITIZE_STRING) ;
        $l = new Liste();
        $l->titre = $titre;
        $l->description = $description;
        $l->save();
        
        $url_listes = $this->app->router->pathFor( 'aff_listes' ) ;    
        return $rs->withRedirect($url_listes); 
	}

	public function addMessage(Request $rq, Response $rs, $args) {
		// Ajout d'un message
		return $rs;
	}		
}

?>