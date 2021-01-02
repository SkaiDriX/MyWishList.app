<?php
namespace mywishlist\controls;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \mywishlist\models\Liste as Liste;
use \mywishlist\views\VueListe as VueListe;

use DateTime;
use mywishlist\models\ListeMessage;

class ListeControleur
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

        $id = $args['id'];

        $item = Item::where(['id' => $id, 'liste_id' => $liste->id])->first();

        if (is_null($item)) {
            $this->app->flash->addMessage('Alerte', 'L item n\'existe pas !');
            return $rs->withRedirect($this->app->router->pathFor('accueil'));
        }
        var_dump($item);
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

        /**affichage de la vue**/

        return $rs;

    }

}

?>