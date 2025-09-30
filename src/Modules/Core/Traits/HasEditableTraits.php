<?php

namespace App\Modules\Core\Traits;


trait HasEditableTraits
{

   public function getEditableAttribute($value)
    {
        return  $value;// ? 'Yes' : 'No';
    }




    public function setDisplayNameAttribute($value)
    {
        $this->attributes['display_name'] = $value;
        $this->attributes['name'] = strtolower(str_replace(" ", "_", $value));
        $this->attributes['editable'] = "Yes";
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower(str_replace(" ", "_", $value));
        $this->attributes['editable'] = "Yes";
    }



    public static function getIdByName($name)
    {
        $name = strtolower($name);
        return self::where('name', $name)->value('id') ?? 0; // Defaults to 0 if not found
    }



}
