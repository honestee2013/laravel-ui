<?php

namespace App\Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'teams';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
                  'name',
                  'description'
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
