<?php

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Geolocation extends Model
{
    use HasFactory;
    
    

    protected $table = 'geolocations';


    protected $fillable = [
        'name', 'address', 'description' // Fillable properties will be inserted here
    ];

     // Relations will be inserted here

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Core\Database\Factories\GeolocationFactory::new();
    }
}
