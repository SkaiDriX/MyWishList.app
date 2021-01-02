<?php
namespace mywishlist\models;

class Item extends \Illuminate\Database\Eloquent\Model{

    protected $table = 'item';
    protected $primaryKey = 'id' ;

    public $timestamps = false;

    public function reservation() {
        return $this->hasOne('mywishlist\models\Reservation', 'id_item');
    }

    public function isReserved() {
        $res = true;
        if(is_null($this->reservation)){
            $res = false;
        }
        return $res;

    }
}

?>