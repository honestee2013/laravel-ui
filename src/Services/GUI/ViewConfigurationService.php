<?php
// app/Services/ViewConfigurationService.php

namespace QuickerFaster\LaravelUI\Services\GUI;

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class ViewConfigurationService
{
    /*protected $configs = [];

    public function getConfig($modelName)
    {
        if (!isset($this->configs[$modelName])) {
            $path = config_path("view-configs/{$modelName}.yaml");
            if (File::exists($path)) {
                $this->configs[$modelName] = Yaml::parseFile($path);
            }
        }
        return $this->configs[$modelName] ?? null;
    }*/

    public function getFieldLabel($field)
    {
        $labels = [
            'employee.first_name' => 'Employee',
            'employee.last_name' => 'Employee',
            'net_hours' => 'Total Hours',
            'is_approved' => 'Approval Status',
            'needs_review' => 'Review Status',
            'date' => 'Date',
            'status' => 'Status',
            'schedule_date' => 'Scheduled Date',
            'shift.name' => 'Shift',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'duration_hours' => 'Duration',
        ];
        
        return $labels[$field] ?? str_replace('_', ' ', ucfirst($field));
    }

public function getBadgeColor($value, $badgeType = null)
{
    // Note: Use string keys "true"/"false" to match YAML parser output
    $colors = [
        'is_approved' => [
            'true'  => 'success',
            'false' => 'warning',
        ],
        'needs_review' => [
            'true'  => 'danger',
            'false' => 'secondary',
        ],
        'status' => [
            'scheduled'   => 'secondary',
            'confirmed'   => 'success',
            'active'   => 'success',
            'cancelled'   => 'danger',
            'completed'   => 'info',
            'swapped'     => 'warning',
            'no_show'     => 'dark',
            'approved'    => 'success',
            'is_approved' => 'success',
            'pending'     => 'warning',
            'rejected'    => 'danger',
        ],
        'is_adjusted' => [
            'true'  => 'warning',
            'false' => 'secondary',
        ],
        'is_active' => [
            'true'  => 'success',
            'false' => 'secondary',
        ],
        'is_default' => [
            'true'  => 'warning',
            'false' => 'secondary',
        ],
        'is_paid_holiday' => [
            'true'  => 'success',
            'false' => 'secondary',
        ],
    ];

    // 1. Normalize $value to handle booleans or 0/1 from Database
    $lookupKey = $value;
    if (is_bool($value)) {
        $lookupKey = $value ? 'true' : 'false';
    } elseif ($value === 1 || $value === 0) {
        // Handle cases where DB returns integers for booleans
        $lookupKey = $value === 1 ? 'true' : 'false';
    }

    // 2. Resolve the specific color set
    $typeColors = $colors[$badgeType] ?? $colors['status'] ?? [];

    // 3. Return the match or a default fallback
    return $typeColors[strtolower($lookupKey)] ?? 'secondary';
}


    public function formatValue($value, $fieldType = null)
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('M d, Y h:i A');
        }
        
        if (is_array($value)) {
            return implode(', ', $value);
        }
        
        return $value ?? '—';
    }
}