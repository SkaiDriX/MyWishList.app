<?php
namespace mywishlist\models;

class Reservation extends \Illuminate\Database\Eloquent\Model{

    protected $table = 'reservations';
    protected $primaryKey = 'id' ;

    public $timestamps = false;


}

?>