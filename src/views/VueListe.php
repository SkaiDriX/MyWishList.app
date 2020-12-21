<?php

namespace mywishlist\vues;

class VueListe {
	private $args;
	private $container; 

    public function __construct($args, $container) {
		$this->args = $args;
		$this->container = $container;
    }
    
    private function formulaireEdition() : string {
        $url_postEdition = $this->container->router->pathFor('edition_liste_post', [
            'tokenPublic' => $this->args['clePublique'],
            'tokenPrivate' => $this->args['clePrive'],
        ]);		

        $nomliste = $this->args['liste']->titre;
        $descliste = $this->args['liste']->description;

		$html = <<<FIN
<form method="POST" action="$url_postEdition">
    <label>Nom de la liste :<br> <textarea rows="5" cols="33" name="titre">$nomliste</textarea></label><br>
    <label>Description de la liste :<br> <textarea rows="5" cols="33" name="desc">$descliste</textarea></label><br>
	<button type="submit">Modifier</button>
</form>	
FIN;
		return $html;
    }
    
    public function render() {
        $content = $this->formulaireEdition();

        $html = <<<FIN
        <!DOCTYPE html>
        <html>
          <head>
            <title>Exemple</title>
          </head>
          <body>
            <h1>Édition de la liste</h1>
            $content
          </body>
        </html>
FIN;
        return $html;
    }
}