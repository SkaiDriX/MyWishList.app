<?php
namespace mywishlist\controls;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

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
		$rs->getBody()->write('Page d\'édition de la liste') ;
		return $rs;
	}		

	public function updateListe(Request $rq, Response $rs, $args) {
		// Modification de la liste
		return $rs;
	}

	public function addMessage(Request $rq, Response $rs, $args) {
		// Ajout d'un message
		return $rs;
	}		
}

?>