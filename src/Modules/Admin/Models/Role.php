<?php

namespace App\Modules\Admin\Models;

use App\Modules\Admin\Models\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;



//use QuickerFaster\LaravelUI\Modules\System\Traits\HasEditableTraits;
use Spatie\Permission\Models\Role as SpatieRole;


class Role extends SpatieRole
{

    //use HasEditableTraits;
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
    * The database primary key value.
    *
    * @var string
    */
    /*protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;*/


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
                'name',
                'description',

                'guard_name',
                'editable',
                'team_id'
              ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];






    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_has_permissions',
            'role_id',
            'permission_id'
        );
    }


    protected static function newFactory()
    {
        return \App\Modules\Admin\Database\Factories\RoleFactory::new();
    }




    /*
     * Get the team for this model.
     *
     * @return App\Models\Team
     * /
    public function team()
    {
        return $this->belongsTo('App\Models\Team','team_id');
    }

    /**
     * Get the modelHasRole for this model.
     *
     * @return App\Models\ModelHasRole
     * /
    public function modelHasRole()
    {
        return $this->hasOne('App\Models\ModelHasRole','role_id','id');
    }

    /**
     * Get the roleHasPermission for this model.
     *
     * @return App\Models\RoleHasPermission
     * /
    public function roleHasPermission()
    {
        return $this->hasOne('App\Models\RoleHasPermission','role_id','id');
    }


    /**
     * Get created_at in array format
     *
     * @param  string  $value
     * @return array
     * /
    public function getCreatedAtAttribute($value)
    {
            $date = \DateTime::createFromFormat($this->getDateFormat(), $value);
    if ($date)
        return $date->format('j/n/Y G:i A');

    }

    /**
     * Get updated_at in array format
     *
     * @param  string  $value
     * @return array
     * /
    public function getUpdatedAtAttribute($value)
    {
            $date = \DateTime::createFromFormat($this->getDateFormat(), $value);
    if ($date)
        return $date->format('j/n/Y G:i A');

    }*/

}
