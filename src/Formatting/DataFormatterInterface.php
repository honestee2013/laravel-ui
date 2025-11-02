<?php

namespace QuickerFaster\LaravelUI\Formatting;

interface DataFormatterInterface
{
    /**
     * Format a raw value for display.
     *
     * @param  mixed  $value   The raw database value
     * @param  mixed  $row     The full model/row (optional, for context)
     * @return string          Formatted string for display
     */
    public function format($value, $row = null): string;
}