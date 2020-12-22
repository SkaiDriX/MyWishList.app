<?php

namespace mywishlist\views;

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

    public function render($i = null)
    {
        $html = <<<FIN
        <!DOCTYPE html>
    <html>
        <head>
            <title>$this->titre_page</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
        </head>
        <body>
            <header>
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <div class="container-fluid">
                <a class="navbar-brand fs-2" href="#">MyWishList.app</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Cr&eacute;er une liste</a>
                    </li>
                  </li>
                </ul>
              </div>
            </div>
          </nav>
              
        </header>
        $this->content
    </body>
</html>
FIN;
        return $html;
    }
}
