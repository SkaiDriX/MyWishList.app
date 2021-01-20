<?php

use \mywishlist\controls\ListeControleur as ListeControleur;
use \mywishlist\controls\PageControleur as PageControleur;
use \mywishlist\controls\ItemControleur as ItemControleur;
use \mywishlist\controls\AuthControleur as AuthControleur;

use Slim\Flash\Messages;

require './vendor/autoload.php';

// On initialise les sessions PHP qui sont utilisées pour faire fonctionner Slim/Flash
session_start(); 

// Configuration de Slim
$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

// Configuration de la BDD
$db = new \Illuminate\Database\Capsule\Manager();
$db->addConnection(parse_ini_file('./conf/conf.ini'));
$db->setAsGlobal();
$db->bootEloquent();

// Création de l'application Slim
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

// Configuration des messages FLASH de Slim
$container = $app->getContainer();

$container['flash'] = function () {
    return new Messages();
};

// Création des routes
$app->get('/', PageControleur::class.':index')->setName('accueil');

/**
 * Routes listes
 */

$app->get('/liste/c/create', ListeControleur::class.':createListe')->setName('create_liste');
$app->post('/liste/c/create', ListeControleur::class.':insertListe')->setName('create_liste_post');

$app->get('/liste/{tokenPublic:[a-zA-Z0-9]+}', ListeControleur::class.':getListe')->setName('affichage_liste');

$app->get('/liste/{tokenPublic:[a-zA-Z0-9]+}/edit/{tokenPrivate:[a-zA-Z0-9]+}', ListeControleur::class.':editListe')->setName('edition_liste');
$app->post('/liste/{tokenPublic:[a-zA-Z0-9]+}/edit/{tokenPrivate:[a-zA-Z0-9]+}', ListeControleur::class.':updateListe')->setName('edition_liste_post');

$app->post('/liste/{tokenPublic:[a-zA-Z0-9]+}/addMessage', ListeControleur::class.':addMessage')->setName('add_message_post');


/**
 * Routes items
 */
 
$app->get('/liste/{tokenPublic:[a-zA-Z0-9]+}/item/{idItem:[0-9]+}', ItemControleur::class.':getItem')->setName('affichage_item');

$app->post('/liste/{tokenPublic:[a-zA-Z0-9]+}/item/{idItem:[0-9]+}/reserve', ItemControleur::class.':reserverItem')->setName('reservation_item');
$app->get('/liste/{tokenPublic:[a-zA-Z0-9]+}/edit/{tokenPrivate:[a-zA-Z0-9]+}/item/{idItem:[0-9]+}/delete', ItemControleur::class.':deleteItem')->setName('suppression_item');

$app->get('/liste/{tokenPublic:[a-zA-Z0-9]+}/edit/{tokenPrivate:[a-zA-Z0-9]+}/item/add', ItemControleur::class.':createItem')->setName('creer_item');
$app->post('/liste/{tokenPublic:[a-zA-Z0-9]+}/edit/{tokenPrivate:[a-zA-Z0-9]+}/item/add', ItemControleur::class.':insertItem')->setName('creer_item_post');

$app->get('/liste/{tokenPublic:[a-zA-Z0-9]+}/edit/{tokenPrivate:[a-zA-Z0-9]+}/item/{idItem:[0-9]+}/edit', ItemControleur::class.':editItem')->setName('edition_item');
$app->post('/liste/{tokenPublic:[a-zA-Z0-9]+}/edit/{tokenPrivate:[a-zA-Z0-9]+}/item/{idItem:[0-9]+}/edit', ItemControleur::class.':updateItem')->setName('edition_item_post');


/**
 * Routes authentification
 */
$app->get('/account/login', AuthControleur::class.':login')->setName('connexion');
$app->post('/account/login', AuthControleur::class.':goLogin')->setName('connexion_post');
 
$app->get('/account/register', AuthControleur::class.':register')->setName('inscription');
$app->post('/account/register', AuthControleur::class.':goRegister')->setName('inscription_post');

$app->get('/account/logout', AuthControleur::class.':logout')->setName('deconnexion');
$app->get('/account/delete', AuthControleur::class.':delete')->setName('delete_account');

$app->get('/account', AuthControleur::class.':infos')->setName('account');

// Lancement de l'application
$app->run();

?>