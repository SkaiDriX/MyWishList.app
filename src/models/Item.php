<?php
namespace mywishlist\models;

use DateTime;

class Item extends \Illuminate\Database\Eloquent\Model{

    protected $table = 'item';
    protected $primaryKey = 'id' ;

    public $timestamps = false;

    public function reservation() {
        return $this->hasOne(‘Reservation’, 'id_item');
    }

    public function isReserved() {
        $res = true;
        if ($this->reservation() == null){
            $res = false;
        }
        return $res;

    }
}

?>