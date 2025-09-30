<?php
namespace QuickerFaster\LaravelUI\Services;

use Illuminate\Support\Str;

class ReadableIdGenerator
{
    public static function generate(string $prefix, string $model): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $count = app($model)::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        return strtoupper($prefix) . "-$year-$month-" . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    public static function generateUuid(): string
    {
        return (string) Str::uuid();
    }
}
