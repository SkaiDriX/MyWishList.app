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

    /**
     * Méthode page affichage de l'item
     */
    public function getItem(Request $rq, Response $rs, $args)
    {

        // On regarde si la liste existe
        $tokenPublic = $args['tokenPublic'];
        $liste = Liste::where('token', '=', $tokenPublic)->first();

        if (is_null($liste)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] La liste n'existe pas");
            return $rs->withRedirect($this->app->router->pathFor('accueil'));
        }

        // On regarde si l'objet existe
        $id = $args['idItem'];
        $item = Item::where(['id' => $id, 'liste_id' => $liste->id])->first();

        if (is_null($item)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] L'item n'existe pas");
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
        
        // Variable pour indiquer si la liste est expirée ou non
        $data['expired'] = $liste->isExpired();


        $vue = new VueItem($data, $this->app);
        $rs->getBody()->write($vue->render(3));
        return $rs;
    }

    /**
     * Méthode formulaire création item
     */
    public function createItem(Request $rq, Response $rs, $args)
    {
        $tokenPublic = $args['tokenPublic'];
        $tokenPrivate = $args['tokenPrivate'];

        // Vérification que la liste existe
        $liste = Liste::where('token', '=', $tokenPublic)->first();
        if (is_null($liste)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] La liste n'existe pas");
            return $rs->withRedirect($this->app->router->pathFor('accueil'));
        } 
        
        // Vérification que le token d'édition est correct
        if (!$liste->isEditable($tokenPrivate)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Le token de modification n'est pas valide");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        } 

        // Vérification que la liste n'est pas expirée
        if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible d'ajouter un item sur une liste expirée");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        $data['public'] = $tokenPublic;
        $data['private'] = $tokenPrivate;

        $vue = new VueItem($data, $this->app);
        $rs->getBody()->write($vue->render(2));
        return $rs;
    }

    /**
     * Méthode formulaire édition de l'item
     */
    public function editItem(Request $rq, Response $rs, $args)
    {
        $tokenPublic = $args['tokenPublic'];
        $tokenPrivate = $args['tokenPrivate'];

        // Vérification que la liste existe
        $liste = Liste::where('token', '=', $tokenPublic)->first();
        if (is_null($liste)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] La liste n'existe pas");
            return $rs->withRedirect($this->app->router->pathFor('accueil'));
        } 
        
        // Vérification que le token d'édition est correct
        if (!$liste->isEditable($tokenPrivate)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Le token de modification n'est pas valide");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        } 

        // Vérification que la liste n'est pas expirée
        if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible de modifier un item sur une liste expirée");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        // Vérification de l'objet existe
        $id = $args['idItem'];
        $item = Item::where(['id' => $id, 'liste_id' => $liste->id])->first();
        if (is_null($item)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] L'item n'existe pas");
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        // Vérification que l'objet n'est pas réservé
        if ($item->isReserved() == true) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible de modifier un item déjà réservé");
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        $data['public'] = $tokenPublic;
        $data['private'] = $tokenPrivate;
        $data['item'] = $item;

        $vue = new VueItem($data, $this->app);
        $rs->getBody()->write($vue->render(1));
        return $rs;
    }

    /**
     * Méthode réservation de l'item
     */
    public function reserverItem(Request $rq, Response $rs, $args)
    {
        $tokenPublic = $args['tokenPublic'];

        // Vérification que la liste existe
        $liste = Liste::where('token', '=', $tokenPublic)->first();
        if (is_null($liste)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] La liste n'existe pas");
            return $rs->withRedirect($this->app->router->pathFor('accueil'));
        } 
        
        // Vérification que la liste n'est pas expirée
        if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible de réserver un item sur une liste expirée");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        // Vérification de l'objet existe
        $id = $args['idItem'];
        $item = Item::where(['id' => $id, 'liste_id' => $liste->id])->first();
        if (is_null($item)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] L'item n'existe pas");
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        // Vérification que l'objet n'est pas réservé
        if ($item->isReserved() == true) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible de réserver un item déjà réservé");
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        // Traitement donnée reçues
        $post = $rq->getParsedBody();
        $message = filter_var($post['message'], FILTER_SANITIZE_STRING);
        $identite = filter_var($post['identite'], FILTER_SANITIZE_STRING);

        if (strlen($message) < 10) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Le message de réservation doit faire au minimum 10 caractères");
        } else if (strlen($identite) < 5) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Votre pseudo doit faire au minimum 5 caractères");
        } else {
            // Création de la réservation
            $reserv = new Reservation();
            $reserv->nom = $identite;
            $reserv->message = $message;
            $reserv->id_item = $item->id;
            $reserv->date = new DateTime();
            $reserv->save();

            // Création cookie identité
            setcookie("username", serialize($identite), time() + 60 * 60 * 24 * 365 * 10, "/");
            $this->app->flash->addMessage('Ok', "[SUCCÈS] Vous avez réservé l'item");
        }

        return $rs->withRedirect($this->app->router->pathFor('affichage_item', ['tokenPublic' => $tokenPublic, 'idItem' => $item->id]));
    }

    /**
     * Méthode pour la suppresion d'un objet
     */    
    public function deleteItem(Request $rq, Response $rs, $args)
    {
        $tokenPublic = $args['tokenPublic'];
        $tokenPrivate = $args['tokenPrivate'];

        // Vérification que la liste existe
        $liste = Liste::where('token', '=', $tokenPublic)->first();
        if (is_null($liste)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] La liste n'existe pas");
            return $rs->withRedirect($this->app->router->pathFor('accueil'));
        } 
        
        // Vérification que le token d'édition est correct
        if (!$liste->isEditable($tokenPrivate)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Le token de modification n'est pas valide");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        } 

        // Vérification que la liste n'est pas expirée
        if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible de supprimer un item sur une liste expirée");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        // Vérification de l'objet existe
        $id = $args['idItem'];
        $item = Item::where(['id' => $id, 'liste_id' => $liste->id])->first();
        if (is_null($item)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] L'item n'existe pas");
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        // Vérification que l'objet n'est pas réservé
        if ($item->isReserved() == true) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible de supprimer un item déjà réservé");
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        // SUPPRESSION DE L'ITEM
        $item->delete();
        $this->app->flash->addMessage('Ok', "[SUCCÈS] Vous avez supprimé l'item");

        return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
    }


    /**
     * Méthode pour l'ajout d'un nouvel item
     */
    public function insertItem(Request $rq, Response $rs, $args)
    {
        $tokenPublic = $args['tokenPublic'];
        $tokenPrivate = $args['tokenPrivate'];

        // Vérification que la liste existe
        $liste = Liste::where('token', '=', $tokenPublic)->first();
        if (is_null($liste)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] La liste n'existe pas");
            return $rs->withRedirect($this->app->router->pathFor('accueil'));
        } 
        
        // Vérification que le token d'édition est correct
        if (!$liste->isEditable($tokenPrivate)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Le token de modification n'est pas valide");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        } 

        // Vérification que la liste n'est pas expirée
        if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible d'ajouter un item sur une liste expirée");
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
            $this->app->flash->addMessage('Alerte', "[ERREUR] Le titre de l'item doit faire au minimum 5 caractères");
            return $rs->withRedirect($this->app->router->pathFor('creer_item', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
		} else if(strlen($desc) < 5) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] La description de l'item doit faire au minimum 5 caractères");
            return $rs->withRedirect($this->app->router->pathFor('creer_item', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        } else if($tarif <= 0 || $tarif >=100000) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Le prix doit être entre 0 et 100.000 euros.");
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
        $this->app->flash->addMessage('Ok', "[SUCCÈS] Vous avez ajouté un item");

        return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
    }

    /**
     * Méthode pour la mise un jour d'un item
    */
    public function updateItem(Request $rq, Response $rs, $args)
    {
        $tokenPublic = $args['tokenPublic'];
        $tokenPrivate = $args['tokenPrivate'];

        // Vérification que la liste existe
        $liste = Liste::where('token', '=', $tokenPublic)->first();
        if (is_null($liste)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] La liste n'existe pas");
            return $rs->withRedirect($this->app->router->pathFor('accueil'));
        } 
        
        // Vérification que le token d'édition est correct
        if (!$liste->isEditable($tokenPrivate)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Le token de modification n'est pas valide");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        } 

        // Vérification que la liste n'est pas expirée
        if ($liste->isExpired()) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible de modifier un item sur une liste expirée");
            return $rs->withRedirect($this->app->router->pathFor('affichage_liste', ['tokenPublic' => $tokenPublic]));
        }

        // Vérification de l'objet existe
        $id = $args['idItem'];
        $item = Item::where(['id' => $id, 'liste_id' => $liste->id])->first();
        if (is_null($item)) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] L'item n'existe pas");
            return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
        }

        // Vérification que l'objet n'est pas réservé
        if ($item->isReserved() == true) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Impossible de modifier un item déjà réservé");
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
            $this->app->flash->addMessage('Alerte', "[ERREUR] Le titre de l'item doit faire au minimum 5 caractères");
            return $rs->withRedirect($this->app->router->pathFor('edition_item', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate, 'idItem' => $id]));
		} else if(strlen($desc) < 5) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] La description de l'item doit faire au minimum 5 caractères");
            return $rs->withRedirect($this->app->router->pathFor('edition_item', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate, 'idItem' => $id]));
        } else if($tarif <= 0 || $tarif >=100000) {
            $this->app->flash->addMessage('Alerte', "[ERREUR] Le prix doit être entre 0 et 100.000 euros.");
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
        $this->app->flash->addMessage('Ok', "[SUCCÈS] Vous avez modifié un item");

        return $rs->withRedirect($this->app->router->pathFor('edition_liste', ['tokenPublic' => $tokenPublic, 'tokenPrivate' => $tokenPrivate]));
    }
}
