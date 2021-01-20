<?php

namespace mywishlist\views;

use finfo;

abstract class Vue
{
    protected $data;
    protected $container;
    protected $content;
    protected $titre_page;

    public function __construct($data, $container)
    {
        $this->data = $data;
        $this->container = $container;
    }

    /**
     * Méthode qui nous permet de récupèrer les alertes s'il y en à
     */
    public function getAlert()
    {
        // On regarde si on a un message d'alerte (alerte ou succès)
        $messages = $this->container->flash->getMessages();

        $html = "";

        if (sizeof($messages) != 0) {
            if(array_key_exists('Alerte',$messages)) {
                $html = '
                <div class="alert alert-danger mt-2">
                ' . $messages["Alerte"][0] . '
                 </div>';
            } else {
                $html = '
                <div class="alert alert-success mt-2">
                    ' . $messages["Ok"][0] . '
                 </div>';
            }
        }
        return $html;
    }

     /**
     * Méthode pour récuperer le menu, côté utilisateur
     */
    public function getUserMenu()
    {
        if(isset($_SESSION['idUser'])) {
            $lienAccount = $this->container->router->pathFor('account');       
            
            $html = <<<FIN
            <li class="nav-item">
                <a class="nav-link" href="$lienAccount">Mon compte</a>
            </li>
FIN;
        } else {
            $lienRegister = $this->container->router->pathFor('inscription');
            $lienConnect = $this->container->router->pathFor('connexion');       

            $html = <<<FIN
            <li class="nav-item">
                <a class="nav-link" href="$lienRegister">Inscription</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="$lienConnect">Connexion</a>
            </li>      
FIN;
        }


        return $html;
    }

    /**
     * Méthode de rendu
     */
    public function render($i = null)
    {
        $lienCreation = $this->container->router->pathFor('create_liste');
        $lienAccueil = $this->container->router->pathFor('accueil');

        $alertContent = $this->getAlert();

        $usermenu = $this->getUserMenu();

        $html = <<<FIN
<!DOCTYPE html>
<html>
    <head>
        <title>$this->titre_page</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
    </head>
    <body style="background-color:#f1f1f1;">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container text-center">
                <a class="navbar-brand" href="$lienAccueil">MyWishList</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="$lienAccueil">Accueil</a>
                        </li>
                        <li class="nav-item">
                           <a class="nav-link" href="$lienCreation">Créer une liste</a>
                        </li>
                        $usermenu                       
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container">  
            $alertContent 
            $this->content
        </div>
    </body>
</html>
FIN;

        return $html;
    }
}
