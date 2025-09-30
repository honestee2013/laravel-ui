<?php

namespace App\Modules\Core\Traits\Database;


trait DatabaseQueryTrait
{



    public function optionList($hintField, $mainField = 'display_name', array $filters = [])
    {
        $query = $this::query();

        if (isset($filters)) {
            foreach ($filters as $filter) {
                $query->where($filter[0], $filter[1], $filter[2]);
            }
        }

        return $query->get()->mapWithKeys(function ($obj)use($hintField, $mainField) {
            $key = $obj->id;
            $val = $obj->$mainField." (".$obj->$hintField. ")";
            return [ $key => $val ];
        })->toArray();
    }

}
