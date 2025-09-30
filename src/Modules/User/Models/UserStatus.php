<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use QuickerFaster\CodeGen\Traits\GUI\HasDisplayName;
use App\Modules\Core\Traits\HasEditableTraits;

class UserStatus extends Model
{
    use HasFactory;
    use HasDisplayName, HasEditableTraits;
    

    protected $table = 'user_statuses';


    protected $fillable = [
        'name', 'description', 'user_status_category_id', 'editable' // Fillable properties will be inserted here
    ];

       public function category(){
		return $this->belongsTo('App\Modules\User\Models\UserStatusCategory', 'user_status_category_id');
	}

 // Relations will be inserted here

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\User\Database\Factories\UserStatusFactory::new();
    }
}
