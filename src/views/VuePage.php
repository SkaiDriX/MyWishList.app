<?php

namespace mywishlist\views;

class VuePage {
	private $args;
	private $container; 

    public function __construct($args, $container) {
		$this->args = $args;
		$this->container = $container;
    }
    
    private function listePublique() : string {
      $html = "<ul>";
      
      foreach($this->args as $data) {
        $html = $html."<li>".$data['titre']."</li>";
      }  

      $html = $html."</ul>";
		  return $html;
    }
    
    public function render() {
        $content = $this->listePublique();

        $html = <<<FIN
        <!DOCTYPE html>
        <html>
          <head>
            <title>Exemple</title>
          </head>
          <body>
            <h1>Accueil</h1>
            $content
          </body>
        </html>
FIN;
        return $html;
    }
}