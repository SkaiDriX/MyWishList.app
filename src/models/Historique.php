<?php
    namespace treasury\models;

    class Historique extends \Illuminate\Database\Eloquent\Model {

        protected $table = 'transaction';
        protected $primaryKey = 'idTransaction';

        public $timestamps = false;

    }
?>