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
          </head>
          <body>
          AVANT CONTENT
          $this->content
          </body>
        </html>
FIN;
        return $html;
    }
}
