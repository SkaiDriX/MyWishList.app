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
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        $data['public'] = $tokenPublic;
        $data['item'] = $item;

        // On récupère la réservation sur l'item si elle existe
        $data['reserved'] = $item->isReserved();
        if ($data['reserved']) {
            $data['reservation'] = $item->reservation;
        }

        // On regarde si l'utilisateur a déjà un pseudo
        $data['identite'] = "";
        if (isset($_COOKIE['username'])) {
            $data['identite'] = unserialize($_COOKIE['username']);
        }

        // On regarde si l'utilisateur est le créateur de la liste
		$data['isOwner'] = 0;

		if (isset($_COOKIE['createdListe'])) {
			$listeTable = unserialize($_COOKIE['createdListe']);
			$listeTable = explode(";", $listeTable);
			if (in_array($liste->token_edit, $listeTable)) {
				$data['isOwner'] = 1;
			}
		}

        $vue = new VueItem($data, $this->app);
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

        $vue = new VueItem($data, $this->app);
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
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        if ($item->isReserved() == true) {
            $this->app->flash->addMessage('Alerte', 'L item est déjà réservé !');
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        $data['public'] = $tokenPublic;
        $data['private'] = $tokenPrivate;
        $data['item'] = $item;

        $vue = new VueItem($data, $this->app);
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
        } else if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', 'Il n\'est pas possible de réserver un item d\'une liste expirée !');
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        $id = $args['idItem'];
        $item = Item::where(['id' => $id, 'liste_id' => $liste->id])->first();

        if (is_null($item)) {
            $this->app->flash->addMessage('Alerte', 'L item n\'existe pas !');
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }
        if ($item->isReserved() == true) {
            $this->app->flash->addMessage('Alerte', 'L item est déjà réservé !');
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        $post = $rq->getParsedBody();
        $message = filter_var($post['message'], FILTER_SANITIZE_STRING);
        $identite = filter_var($post['identite'], FILTER_SANITIZE_STRING);

        if (strlen($message) < 10) {
            $this->app->flash->addMessage('Alerte', 'Le message doit au moins faire 10 caractères !');
        } else if (strlen($identite) < 5) {
            $this->app->flash->addMessage('Alerte', 'Votre pseudo doit faire au moins 5 caractères !');
        } else {
            $reserv = new Reservation();
            $reserv->nom = $identite;
            $reserv->message = $message;
            $reserv->id_item = $item->id;
            $reserv->date = new DateTime();
            $reserv->save();

            // Création cookie identité
            setcookie("username", serialize($identite), time() + 60 * 60 * 24 * 365 * 10, "/");

            $this->app->flash->addMessage('Ok', 'Vous avez réserver l\'objet !');
        }

        return $rs->withRedirect($this->app->router->pathFor('affichage_item', ['tokenPublic' => $tokenPublic, 'idItem' => $item->id]));
    }


    
    public function deleteItem(Request $rq, Response $rs, $args)
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
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        if ($item->isReserved() == true) {
            $this->app->flash->addMessage('Alerte', 'L item est déjà réservé !');
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        // SUPPRESSION DE L'ITEM
        $item->delete();
        $this->app->flash->addMessage('Ok', "L'item a été supprimé !");

        return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
    }



    public function insertItem(Request $rq, Response $rs, $args)
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

        // RÉCUPERATION DES VALEURS
        $post = $rq->getParsedBody();
        $titre = filter_var($post['titre'], FILTER_SANITIZE_STRING);
        $desc = filter_var($post['desc'], FILTER_SANITIZE_STRING);
        $url = filter_var($post['url'], FILTER_SANITIZE_STRING);
        $img = filter_var($post['img'], FILTER_SANITIZE_STRING);
        $tarif = filter_var($post['tarif'], FILTER_SANITIZE_STRING);

        // CONTRÔLE DES VALEURS
        if(strlen($titre) < 5) {
            $this->app->flash->addMessage('Alerte', 'Le titre doit au moins faire 5 caractères !');
            return $rs->withRedirect($this->app->router->pathFor('creer_item', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
		} else if(strlen($desc) < 5) {
            $this->app->flash->addMessage('Alerte', 'La description doit au moins faire 5 caractères !');
            return $rs->withRedirect($this->app->router->pathFor('creer_item', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        } else if($tarif <= 0) {
            $this->app->flash->addMessage('Alerte', 'Le prix doit être supérieur à 0 euro !');
            return $rs->withRedirect($this->app->router->pathFor('creer_item', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        } 
        
        // CRÉATION DE L'ITEM
        $item = new Item();
        $item->liste_id = $liste->id;
        $item->nom = $titre;
        $item->descr = $desc;
        $item->img = $img;
        $item->url = $url;
        $item->tarif = $tarif;
        $item->save();
        $this->app->flash->addMessage('Ok', "L'item a été ajouté !");

        return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
    }




    public function updateItem(Request $rq, Response $rs, $args)
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
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        if ($item->isReserved() == true) {
            $this->app->flash->addMessage('Alerte', 'L item est déjà réservé !');
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        // RÉCUPERATION DES VALEURS
        $post = $rq->getParsedBody();
        $titre = filter_var($post['titre'], FILTER_SANITIZE_STRING);
        $desc = filter_var($post['desc'], FILTER_SANITIZE_STRING);
        $url = filter_var($post['url'], FILTER_SANITIZE_STRING);
        $img = filter_var($post['img'], FILTER_SANITIZE_STRING);
        $tarif = filter_var($post['tarif'], FILTER_SANITIZE_STRING);

        // CONTRÔLE DES VALEURS
        if(strlen($titre) < 5) {
            $this->app->flash->addMessage('Alerte', 'Le titre doit au moins faire 5 caractères !');
            return $rs->withRedirect($this->app->router->pathFor('edition_item', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate, 'idItem' => $id]));
		} else if(strlen($desc) < 5) {
            $this->app->flash->addMessage('Alerte', 'La description doit au moins faire 5 caractères !');
            return $rs->withRedirect($this->app->router->pathFor('edition_item', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate, 'idItem' => $id]));
        } else if($tarif <= 0) {
            $this->app->flash->addMessage('Alerte', 'Le prix doit être supérieur à 0 euro !');
            return $rs->withRedirect($this->app->router->pathFor('edition_item', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate, 'idItem' => $id]));
        } 

        // MODIFICATION DE L'ITEM
        $item->liste_id = $liste->id;
        $item->nom = $titre;
        $item->descr = $desc;
        $item->img = $img;
        $item->url = $url;
        $item->tarif = $tarif;
        $item->save();
        $this->app->flash->addMessage('Ok', "L'item a été modifié !");

        return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
    }
}
