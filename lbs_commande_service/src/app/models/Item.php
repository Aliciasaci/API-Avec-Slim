<?php

namespace lbs\command\app\models;

class Item extends \Illuminate\Database\Eloquent\Model {

    protected $table      = 'item'; 
    protected $primaryKey = 'id';
    public  $incrementing = true;   
    public $timestamps = false;

    public function commande() {
        return $this->belongsTo('\lbs\command\app\models\Commande', 'command_id');
    }

}