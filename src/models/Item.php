<?php
namespace mywishlist\models;

class Item extends \Illuminate\Database\Eloquent\Model{

    protected $table = 'item';
    protected $primaryKey = 'id' ;

    public $timestamps = false;

    /**
     * Permet de récupérer la réservation de l'item si elle existe
     */
    public function reservation() {
        return $this->hasOne('mywishlist\models\Reservation', 'id_item');
    }

    /**
     * Méthode qui permet de savoir si l'item est réservé ou non
     */
    public function isReserved() {
        $res = true;
        if(is_null($this->reservation)){
            $res = false;
        }
        return $res;

    }
}

?>