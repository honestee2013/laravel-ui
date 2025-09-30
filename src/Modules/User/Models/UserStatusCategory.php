<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class UserStatusCategory extends Model
{
    use HasFactory;
    
    

    protected $table = 'user_status_categories';


    protected $fillable = [
        'name', 'description', 'editable' // Fillable properties will be inserted here
    ];

       public function userStatuses(){
		return $this->hasMany('App\Modules\User\Models\UserStatus');
	}

 // Relations will be inserted here

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\User\Database\Factories\UserStatusCategoryFactory::new();
    }
}
