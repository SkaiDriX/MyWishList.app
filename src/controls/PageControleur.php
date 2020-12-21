<?php
namespace mywishlist\controls;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \mywishlist\models\Liste as Liste;

use \mywishlist\vues\VuePage as VuePage;

class PageControleur {
	private $app;
	
	public function __construct($app) {
		$this->app = $app;
	}

	public function index(Request $rq, Response $rs, $args) {
		$data = Liste::select('titre', 'description', 'token') -> where ('publique', '=', 1)->get()->toArray();

		$vue = new VuePage($data, $this->app ) ;
		$rs->getBody()->write($vue->render()) ;
		return $rs;
	}
}
?>