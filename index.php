<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \mywishlist\controls\ListeControleur as ListeControleur;
use \mywishlist\controls\PageControleur as PageControleur;

require './vendor/autoload.php';

// Configuration SLIM
$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

// Configuration BDD
$db = new \Illuminate\Database\Capsule\Manager();
$db->addConnection(parse_ini_file('./conf/conf.ini'));
$db->setAsGlobal();
$db->bootEloquent();

//var_dump($db);

// Application SLIM
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

// Les routes
$app->get('/', ListeControleur::class.':index');

$app->get('/liste/c/create',
    function (Request $req, Response $resp, $args) {
        $lc = new ListeControleur();
        $lc->createListe();
        return $resp;
    }
);

$app->post('/liste/c/create',
 function (Request $req, Response $resp, $args) {
 $resp->getBody()->write("création liste");
 return $resp;
 }
);

$app->get('/liste/{tokenPublic}',
 function (Request $req, Response $resp, $args) {
    $token = $args['tokenPublic'];
    $resp->getBody()->write("Affichage de la liste avec le token <b>$token</b>");
 return $resp;
 }
);

$app->post('/liste/{tokenPublic}/addMessage',
 function (Request $req, Response $resp, $args) {
    $token = $args['tokenPublic'];
    $resp->getBody()->write("Ajout d'un message sur la liste ayant le token <b>$token</b>");
 return $resp;
 }
);

$app->get('/liste/{tokenPublic}/edit/{tokenPrive}',
 function (Request $req, Response $resp, $args) {
    $token = $args['tokenPublic'];
    $resp->getBody()->write("Modification de la liste de token public <b>$token</b>");
 return $resp;
 }
);

$app->post('/liste/{tokenPublic}/edit/{tokenPrive}',
 function (Request $req, Response $resp, $args) {
    $token = $args['tokenPublic'];
    $resp->getBody()->write("Redirection vers la liste de token <b>$token</b>");
 return $resp;
 }
);

$app->run();

?>