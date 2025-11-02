<?php

namespace QuickerFaster\LaravelUI\Formatting;

class CurrencyFormatter implements DataFormatterInterface
{
    public function format($value, $row = null): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return '$' . number_format((float) $value, 2);
    }
}