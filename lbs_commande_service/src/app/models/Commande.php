<?php

namespace lbs\command\app\models;

class Commande extends \Illuminate\Database\Eloquent\Model {

    protected $table      = 'commande';  /* le nom de la table */
    protected $primaryKey = 'id';
    public  $incrementing = false;      //Pour la clé primaire (id) on annule l'auto incrémentation
    public $keyType='string';           

    public function items() {
        return $this->hasMany('\lbs\command\app\models\Item', 'command_id');
    }


} 