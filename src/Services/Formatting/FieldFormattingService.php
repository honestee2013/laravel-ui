<?php

namespace QuickerFaster\LaravelUI\Services\Formatting;

use QuickerFaster\LaravelUI\Formatting\DataFormatterInterface;
use Illuminate\Contracts\Container\BindingResolutionException;

class FieldFormattingService
{
    /**
     * Format a field value using metadata config.
     *
     * @param  string  $field   Field name (e.g., 'is_active')
     * @param  mixed   $value   Raw value from model/database
     * @param  mixed   $row     Full model instance (optional)
     * @return string
     */
    public function format(string $field, $value, $fieldDefinitions, $row = null): string
    {
        
        $config = $fieldDefinitions[$field]?? [];

        // If no config or no formatter, return raw value as string
        if (!isset($config['formatter'])) {
            return $value ?? '—';
        }


        $formatterClass = $config['formatter'];

        try {
            /** @var DataFormatterInterface $formatter */
            $formatter = app($formatterClass);
        } catch (BindingResolutionException $e) {
            \Log::warning("Formatter not found or invalid: {$formatterClass}", ['field' => $field]);
            return $value ?? '—';
        }

        if (!$formatter instanceof DataFormatterInterface) {
            \Log::warning("Formatter does not implement DataFormatterInterface", [
                'formatter' => $formatterClass,
                'field' => $field
            ]);
            return $value ?? '—';
        }



        return $formatter->format($value, $row);
    }

    /**
     * Format multiple fields at once.
     *
     * @param  array  $data   Associative array of field => value
     * @param  mixed  $row    Full model (optional)
     * @return array
     */
    public function formatMany(array $data, $row = null): array
    {
        $formatted = [];
        foreach ($data as $field => $value) {
            $formatted[$field] = $this->format($field, $value, $row);
        }
        return $formatted;
    }
}