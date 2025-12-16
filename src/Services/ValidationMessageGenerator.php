<?php

namespace QuickerFaster\LaravelUI\Services;

use Illuminate\Support\Str;

class ValidationMessageGenerator
{
    /**
     * Generate human-readable validation messages from rules and field definitions.
     *
     * @param array $prefixedRules e.g. ['formData.hr_employee.email' => 'required|email|unique:users,email']
     * @param array $fieldDefinitions e.g. ['email' => ['label' => 'Work Email', ...]]
     * @param string $modelAlias e.g. 'hr_employee'
     * @return array
     */
    public function generateMessages(
        array $prefixedRules,
        array $fieldDefinitions,
        string $modelAlias
    ): array {
        $messages = [];

        foreach ($prefixedRules as $fullKey => $rules) {
            // Extract field name: formData.hr_employee.first_name → first_name
            $fieldName = $this->extractFieldName($fullKey, $modelAlias);
            if ($fieldName === null)
                continue;

            // Get human-readable label
            $label = $this->getFieldLabel($fieldDefinitions, $fieldName);

            // Normalize rules into array
            $ruleList = $this->parseRules($rules);

            foreach ($ruleList as $rule) {
                [$ruleName, $parameters] = $this->parseRule($rule);
                if (!$ruleName)
                    continue;

                $messageKey = "{$fullKey}.{$ruleName}";
                $messages[$messageKey] = $this->buildMessage($ruleName, $parameters, $label);
            }
        }

        return $messages;
    }

    protected function extractFieldName(string $fullKey, string $modelAlias): ?string
    {
        /*$parts = explode('.', $fullKey);
        if (count($parts) === 3 && $parts[0] === 'formData' && $parts[1] === $modelAlias) {
            return $parts[2];
        }
        return null;*/

        $parts = explode('.', $fullKey);

        if (count($parts) === 2 && $parts[0] === $modelAlias) {
            return $parts[1];
        }
        return null;

    }

    protected function getFieldLabel(array $fieldDefinitions, string $fieldName): string
    {
        if (isset($fieldDefinitions[$fieldName]['label'])) {
            return $fieldDefinitions[$fieldName]['label'];
        }
        return ucwords(str_replace('_', ' ', $fieldName));

        // For multiple language support use the following code instead:
        /*    $key = "validation.attributes.{$fieldName}";
        if (isset($fieldDefinitions[$fieldName]['label'])) {
            return __($key, [], $fieldDefinitions[$fieldName]['label']);
        }
        return __($key, [], ucwords(str_replace('_', ' ', $fieldName)));*/

    }





    protected function parseRules($rules): array
    {
        if (is_array($rules)) {
            return $rules;
        }
        return array_filter(array_map('trim', explode('|', $rules)));
    }

    protected function parseRule(string $rule): array
    {
        if (strpos($rule, ':') !== false) {
            [$name, $params] = explode(':', $rule, 2);
            $parameters = str_getcsv($params, ',', '"'); // handles "foo,bar,baz"
            return [strtolower($name), $parameters];
        }
        return [strtolower($rule), []];
    }

    protected function buildMessage(string $ruleName, array $parameters, string $label): string
    {
        return match ($ruleName) {
            'required' => "The {$label} field is required.",
            'required_if' => "The {$label} field is required when " . ($parameters[0] ?? 'another field') . " is " . ($parameters[1] ?? 'set.'),
            'required_with' => "The {$label} field is required when " . ($parameters[0] ?? 'another field') . " is present.",
            'required_without' => "The {$label} field is required when " . ($parameters[0] ?? 'another field') . " is not present.",
            'accepted' => "You must accept the {$label}.",
            'boolean' => "The {$label} must be true or false.",
            'date' => "Please enter a valid date for {$label}.",
            'date_format' => "The {$label} must match the format: " . ($parameters[0] ?? 'YYYY-MM-DD'),
            'email' => "The {$label} must be a valid email address.",
            'url' => "The {$label} must be a valid URL.",
            'numeric' => "{$label} must be a number.",
            'integer' => "{$label} must be a whole number.",
            'digits' => "{$label} must be exactly {$parameters[0]} digits.",
            'digits_between' => "{$label} must be between {$parameters[0]} and {$parameters[1]} digits.",
            'min' => $this->handleMinRule($parameters, $label),
            'max' => $this->handleMaxRule($parameters, $label),
            'between' => "{$label} must be between {$parameters[0]} and {$parameters[1]}.",
            'size' => "{$label} must be exactly {$parameters[0]}.",
            'in' => "The selected {$label} is invalid.",
            'not_in' => "The selected {$label} is invalid.",
            'regex' => "The {$label} format is invalid.",
            'confirmed' => "The {$label} confirmation does not match.",
            'same' => "The {$label} and " . ($parameters[0] ?? 'other field') . " must match.",
            'different' => "The {$label} and " . ($parameters[0] ?? 'other field') . " must be different.",
            'unique' => $this->handleUniqueRule($parameters, $label),
            'exists' => $this->handleExistsRule($parameters, $label),
            'after' => "The {$label} must be a date after " . ($parameters[0] ?? 'the specified date'),
            'after_or_equal' => "The {$label} must be a date after or equal to " . ($parameters[0] ?? 'the specified date'),
            'before' => "The {$label} must be a date before " . ($parameters[0] ?? 'the specified date'),
            'before_or_equal' => "The {$label} must be a date before or equal to " . ($parameters[0] ?? 'the specified date'),
            'alpha' => "{$label} may only contain letters.",
            'alpha_dash' => "{$label} may only contain letters, numbers, dashes, and underscores.",
            'alpha_num' => "{$label} may only contain letters and numbers.",
            'array' => "{$label} must be an array.",
            'file' => "{$label} must be a file.",
            'image' => "{$label} must be an image.",
            'json' => "{$label} must be valid JSON.",
            'lowercase' => "{$label} must be lowercase.",
            'uppercase' => "{$label} must be uppercase.",
            'timezone' => "{$label} must be a valid timezone.",
            'uuid' => "{$label} must be a valid UUID.",
            default => "The {$label} field is invalid."
        };
    }

    protected function handleMinRule(array $parameters, string $label): string
    {
        if (empty($parameters))
            return "{$label} is too short.";
        $value = $parameters[0];
        // Guess if it's string (chars) or numeric (value)
        return is_numeric($value) && $value > 1000
            ? "{$label} must be at least {$value}."
            : "{$label} must be at least {$value} characters.";
    }

    protected function handleMaxRule(array $parameters, string $label): string
    {
        if (empty($parameters))
            return "{$label} is too long.";
        $value = $parameters[0];
        return is_numeric($value) && $value > 1000
            ? "{$label} may not be greater than {$value}."
            : "{$label} may not be greater than {$value} characters.";
    }

    protected function handleUniqueRule(array $parameters, string $label): string
    {
        $table = $parameters[0] ?? 'records';
        return "The {$label} has already been taken.";
    }

    protected function handleExistsRule(array $parameters, string $label): string
    {
        $table = $parameters[0] ?? 'records';
        return "The selected {$label} is invalid.";
    }
}