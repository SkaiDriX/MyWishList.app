<?php
namespace mywishlist\models;

use DateTime;

class Liste extends \Illuminate\Database\Eloquent\Model{

    protected $table = 'liste';
    protected $primaryKey = 'id' ;

    public $timestamps = false;

    /**
     * Cette méthode permet de savoir si le token d'édition passé en paramètre est bien celui associé à la liste
     */
    public function isEditable($tokenPrive) {
        return ($this->token_edit == $tokenPrive);
    }

    /**
     * Cette méthode permet de savoir si la liste est expirée ou non
     */
    public function isExpired() {
        return (new DateTime($this->expiration) < new DateTime());
    }

    /**
     * Méthode qui permet de récupérer la liste des messages associés à la liste
     */
    public function messages() {
        return $this->hasMany('mywishlist\models\ListeMessage', 'liste_id', 'id')->get()->sortByDesc('date')->sortByDesc('id'); // on trie par date d'expiration et par ID
    }

    /**
     * Méthode qui permet de récupérer la liste des items associés à la liste
     */
    public function items() {
        return $this->hasMany('mywishlist\models\Item', 'liste_id'); 
    }
}

?>