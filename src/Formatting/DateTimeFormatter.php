<?php

namespace QuickerFaster\LaravelUI\Formatting;

use Carbon\CarbonInterface;

class DateTimeFormatter implements DataFormatterInterface
{
    public function format($value, $row = null): string
    {
        if (!$value) {
            return '—';
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('M j, Y g:i A');
        }

        try {
            return \Carbon\Carbon::parse($value)->format('M j, Y g:i A');
        } catch (\Exception $e) {
            return (string) $value;
        }
    }
}