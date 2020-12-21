<?php
namespace mywishlist\controls;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \mywishlist\models\Liste as Liste;

class PageControleur {
	private $app;
	
	public function __construct($app) {
		$this->app = $app;
	}
	public function index(Request $rq, Response $rs, $args) {
		$rs->getBody()->write('Accueil du site<br><br>') ;

		$query = Liste::select('id', 'titre', 'description') -> where ('publique', '=', 1);
		$res = $query->get();
		foreach ($res as $entree) {
			echo '<b>' . $entree->titre . '</b><br>' . $entree->description . '<br><br>';
		}
		return $rs;
	}
}
?>