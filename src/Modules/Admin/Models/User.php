<?php

namespace App\Modules\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class User extends Model
{
    use HasFactory;
    
    



    protected $fillable = [
        'name', 'email', 'email_verified_at', 'password', 'password_confirmation', 'remember_token', 'user_type' // Fillable properties will be inserted here
    ];

    /*public function employeeProfile(){
		return $this->belongsTo('App\Modules\Hr\Models\EmployeeProfile', 'employee_profile_id');
	}*/

 // Relations will be inserted here

    /**
     * Create a new factory instance for the model.
     */
    /*protected static function newFactory()
    {
        return \App\Modules\User\Database\Factories\UserFactory::new();
    }*/
}
