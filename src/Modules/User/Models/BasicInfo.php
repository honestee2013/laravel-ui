<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class BasicInfo extends Model
{
    use HasFactory;
    
    

    protected $table = 'basic_infos';


    protected $fillable = [
        'profile_picture', 'user_id', 'about_me', 'phone_number', 'email', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country', 'date_of_birth', 'gender' // Fillable properties will be inserted here
    ];

       public function user(){
		return $this->belongsTo('App\Models\User', 'user_id');
	}

 // Relations will be inserted here

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\User\Database\Factories\BasicInfoFactory::new();
    }
}
