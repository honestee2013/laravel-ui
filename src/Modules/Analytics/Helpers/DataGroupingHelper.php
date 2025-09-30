<?php

namespace App\Modules\Analytics\Helpers;

use Illuminate\Support\Facades\DB;

class DataGroupingHelper
{
    public static function fetchData($table, $column, $groupBy, $colors = [])
    {
        $query = DB::table($table)
            ->selectRaw("
                CASE
                    WHEN ? = 'daily' THEN DATE({$column})
                    WHEN ? = 'weekly' THEN DATE_FORMAT({$column}, '%u-%Y')
                    WHEN ? = 'monthly' THEN DATE_FORMAT({$column}, '%M')
                    WHEN ? = 'yearly' THEN YEAR({$column})
                END as label,
                COUNT(*) as total
            ", [$groupBy, $groupBy, $groupBy, $groupBy])
            ->groupBy('label')
            ->orderBy('label', 'asc')
            ->get();

        return [
            'labels' => $query->pluck('label'),
            'data' => $query->pluck('total'),
            'colors' => $colors,
        ];
    }

}
