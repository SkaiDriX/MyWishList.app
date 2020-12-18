<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


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

var_dump($db);

// Application SLIM
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

// Les routes
$app->get('/',
 function (Request $req, Response $resp, $args) {
 $resp->getBody()->write("Slim marche wesh !!!");
 return $resp;
 }
);

$app->run();

?>