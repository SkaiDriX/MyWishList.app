<?php
namespace mywishlist\models;

use DateTime;

class Liste extends \Illuminate\Database\Eloquent\Model{

    protected $table = 'liste';
    protected $primaryKey = 'id' ;

    public $timestamps = false;

    public function isEditable($tokenPrive) {
        if($this->token_edit == $tokenPrive) {
            return true;
        }
        return false;
    }

    public function isExpired() {
        if(new DateTime($this->expiration) < new DateTime()) {
            return true;
        }
        
        return false;
    }
}

?>