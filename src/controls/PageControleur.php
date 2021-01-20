<?php
namespace mywishlist\controls;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \mywishlist\models\Liste as Liste;

use \mywishlist\views\VuePage as VuePage;

class PageControleur {
	private $app;
	
	public function __construct($app) {
		$this->app = $app;
	}

	/**
	 * Index du site
	 */
	public function index(Request $rq, Response $rs, $args) {
		// On récupère l'ensemble des listes publiques, on passera ce tableau à la vue
		$data = Liste::where ('publique', '=', 1)->where('expiration', '>', date('Y-m-d'))->get()->sortBy('expiration')->toArray();
		
		$vue = new VuePage($data, $this->app ) ;
		$rs->getBody()->write($vue->render()) ;
		return $rs;
	}
}
?>