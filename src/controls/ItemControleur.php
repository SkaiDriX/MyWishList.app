<?php
namespace mywishlist\controls;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \mywishlist\models\Liste as Liste;
use \mywishlist\models\Reservation as Reservation;
use \mywishlist\models\Item as Item;
use \mywishlist\views\VueItem as VueItem;

use DateTime;

class ItemControleur
{

    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function getItem(Request $rq, Response $rs, $args)
    {

        $tokenPublic = $args['tokenPublic'];
        $liste = Liste::where('token', '=', $tokenPublic)->first();

        if (is_null($liste)) {
            $this->app->flash->addMessage('Alerte', 'La liste n\'existe pas !');
            return $rs->withRedirect($this->app->router->pathFor('accueil'));
        }

        $id = $args['idItem'];
        $item = Item::where(['id' => $id, 'liste_id' => $liste->id])->first();

        if (is_null($item)) {
            $this->app->flash->addMessage('Alerte', 'L item n\'existe pas !');
            return $rs->withRedirect( $this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        $data['public'] = $tokenPublic;
        $data['item'] = $item;

        // On récupère la réservation sur l'item si elle existe
        $data['reserved'] = $item->isReserved(); 
        if($data['reserved']) {
            $data['reservation'] = $item->reservation;
        }

        // On regarde si l'utilisateur a déjà un pseudo
		$data['identite'] = "";
		if (isset($_COOKIE['username'])) {
			$data['identite'] = unserialize($_COOKIE['username']);
		}

        $vue = new VueItem($data, $this->app) ;
		$rs->getBody()->write($vue->render(3));

        return $rs;
    }

    public function createItem(Request $rq, Response $rs, $args)
    {
        $tokenPublic = $args['tokenPublic'];
        $tokenPrivate = $args['tokenPrivate'];

        $liste = Liste::where('token', '=', $tokenPublic)->first();

        if (is_null($liste)) {
            $this->app->flash->addMessage('Alerte', 'La liste n\'existe pas !');
            return $rs->withRedirect($this->app->router->pathFor('accueil'));
        } else if (!$liste->isEditable($tokenPrivate)) {
            $this->app->flash->addMessage('Alerte', 'Le token de modification est invalide !');
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        } else if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', 'Il n\'est pas possible de modifier une liste expirée !');
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        $data['public'] = $tokenPublic;
        $data['private'] = $tokenPrivate;

        $vue = new VueItem($data, $this->app) ;
		$rs->getBody()->write($vue->render(2));
        return $rs;

    }

    public function editItem(Request $rq, Response $rs, $args)
    {
        $tokenPublic = $args['tokenPublic'];
        $tokenPrivate = $args['tokenPrivate'];

        $liste = Liste::where('token', '=', $tokenPublic)->first();

        if (is_null($liste)) {
            $this->app->flash->addMessage('Alerte', 'La liste n\'existe pas !');
            return $rs->withRedirect($this->app->router->pathFor('accueil'));
        } else if (!$liste->isEditable($tokenPrivate)) {
            $this->app->flash->addMessage('Alerte', 'Le token de modification est invalide !');
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        } else if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', 'Il n\'est pas possible de modifier une liste expirée !');
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        $id = $args['idItem'];
        $item = Item::where(['id' => $id, 'liste_id' => $liste->id])->first();

        if (is_null($item)) {
            $this->app->flash->addMessage('Alerte', 'L item n\'existe pas !');
            return $rs->withRedirect($this->app->router->pathFor('edition_liste',[ 'tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        if ($item->isReserved() == true) {
            $this->app->flash->addMessage('Alerte', 'L item est déjà réservé !');
            return $rs->withRedirect($this->app->router->pathFor('edition_liste',[ 'tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        $data['public'] = $tokenPublic;
        $data['private'] = $tokenPrivate;
        $data['item'] = $item;

        $vue = new VueItem($data, $this->app) ;
		$rs->getBody()->write($vue->render(1));
        return $rs;
    }

    public function reserverItem(Request $rq, Response $rs, $args)
    {
        $tokenPublic = $args['tokenPublic'];

        $liste = Liste::where('token', '=', $tokenPublic)->first();

        if (is_null($liste)) {
            $this->app->flash->addMessage('Alerte', 'La liste n\'existe pas !');
            return $rs->withRedirect($this->app->router->pathFor('accueil'));
        }  else if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', 'Il n\'est pas possible de réserver un item d\'une liste expirée !');
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        $id = $args['idItem'];
        $item = Item::where(['id' => $id, 'liste_id' => $liste->id])->first();

        if (is_null($item)) {
            $this->app->flash->addMessage('Alerte', 'L item n\'existe pas !');
            return $rs->withRedirect( $this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }
        if ($item->isReserved() == true) {
            $this->app->flash->addMessage('Alerte', 'L item est déjà réservé !');
            return $rs->withRedirect( $this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        $post = $rq->getParsedBody() ;
        $message = filter_var($post['message'], FILTER_SANITIZE_STRING) ;
        $identite = filter_var($post['identite'] , FILTER_SANITIZE_STRING);

        if(strlen($message) < 10) {
            $this->app->flash->addMessage('Alerte', 'Le message doit au moins faire 10 caractères !');
        } else if(strlen($identite) < 5) {
            $this->app->flash->addMessage('Alerte', 'Votre pseudo doit faire au moins 5 caractères !');
        }  else {
            $reserv = new Reservation();
            $reserv->nom = $identite;
            $reserv->message = $message;
            $reserv->id_item = $item->id;
            $reserv->date = new DateTime();
            $reserv->save();

            // Création cookie identité
            setcookie("username", serialize ($identite),time() + 60*60*24*365*10, "/" ) ;

            $this->app->flash->addMessage('Ok', 'Vous avez réserver l\'objet !');
        }
        
        return $rs->withRedirect( $this->app->router->pathFor('affichage_item', ['tokenPublic' => $tokenPublic, 'idItem' => $item->id]));

    }

}

?>