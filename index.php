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

// Application SLIM
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

// Les routes
$app->get('/', PageControleur::class.':index')->setName('accueil');

$app->get('/liste/c/create', ListeControleur::class.':createListe')->setName('create_liste');
$app->post('/liste/c/create', ListeControleur::class.':insertListe')->setName('create_liste_post');

$app->get('/liste/{tokenPublic}', ListeControleur::class.':getListe')->setName('affichage_liste');

$app->get('/liste/{tokenPublic}/edit/{tokenPrivate}', ListeControleur::class.':editListe')->setName('edition_liste');
$app->post('/liste/{tokenPublic}/edit/{tokenPrivate}', ListeControleur::class.':updateListe')->setName('edition_liste_post');

$app->post('/liste/{tokenPublic}/addMessage', ListeControleur::class.':addMessage')->setName('add_message_post');

$app->run();

?>