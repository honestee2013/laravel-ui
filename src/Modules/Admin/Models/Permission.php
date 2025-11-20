<?php

namespace App\Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Permission\Models\Permission as SpatiePermission;


class Permission extends SpatiePermission
{


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
                  'description',
                  'guard_name',
                  'name'
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

    /**
     * Get the modelHasPermission for this model.
     *
     * @return App\Models\ModelHasPermission
     */
    public function modelHasPermission()
    {
        return $this->hasOne('App\Models\ModelHasPermission','permission_id','id');
    }

    /**
     * Get the roleHasPermission for this model.
     *
     * @return App\Models\RoleHasPermission
     */
    public function roleHasPermission()
    {
        return $this->hasOne('App\Models\RoleHasPermission','permission_id','id');
    }


    /**
     * Get created_at in array format
     *
     * @param  string  $value
     * @return array
     */
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
     */
    public function getUpdatedAtAttribute($value)
    {
            $date = \DateTime::createFromFormat($this->getDateFormat(), $value);
    if ($date)
        return $date->format('j/n/Y G:i A');

    }

}
