<?php
namespace mywishlist\controls;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PageControleur {
	private $app;
	
	public function __construct($app) {
		$this->app = $app;
	}
	public function index(Request $rq, Response $rs, $args) {
		$rs->getBody()->write('Accueil du site') ;
		return $rs;
	}
}
?>