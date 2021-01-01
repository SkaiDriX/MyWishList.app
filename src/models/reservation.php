<?php
namespace mywishlist\models;

use DateTime;

class reservation extends \Illuminate\Database\Eloquent\Model{

    protected $table = 'reservations';
    protected $primaryKey = 'id' ;

    public $timestamps = false;


}

?>