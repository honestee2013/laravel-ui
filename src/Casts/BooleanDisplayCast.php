<?php

namespace QuickerFaster\LaravelUI\Casts;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;



// app/Casts/BooleanDisplayCast.php
class BooleanDisplayCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No';
    }
    
    public function set($model, string $key, $value, array $attributes): int
    {
        return (bool) $value ? 1 : 0;
    }
}
