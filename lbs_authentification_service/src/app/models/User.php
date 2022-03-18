<?php

namespace lbs\authentification\app\models;

class User extends \Illuminate\Database\Eloquent\Model {

    protected $table      = 'user'; 
    protected $primaryKey = 'id';
    public  $incrementing = true;   
    public $timestamps = true;


}