<?php

namespace QuickerFaster\LaravelUI\Formatting;

class BooleanFormatter implements DataFormatterInterface
{
    public function format($value, $row = null): string
    {
        
        if ($value === null) {
            return '—';
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No';
    }
}