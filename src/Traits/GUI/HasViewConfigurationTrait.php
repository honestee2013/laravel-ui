<?php
// app/Traits/ViewConfigurationTrait.php

namespace QuickerFaster\LaravelUI\Traits\GUI;

trait HasViewConfigurationTrait
{
    // protected $viewConfig;
    
    /*public function mountViewConfiguration($model = null)
    {
        if ($model) {
            $configService = app(\App\Services\ViewConfigurationService::class);
            $this->viewConfig = $configService->getConfig(class_basename($model));
        }
    }*/
    
    public function getAvatarUrl($record, $field = null)
    {
        if ($field && data_get($record, $field)) {
            return asset('storage/' . data_get($record, $field));
        }
        
        // Try common photo fields
        $photoFields = ['photo', 'profile_picture', 'avatar', 'image', 'photo_url'];
        foreach ($photoFields as $field) {
            if ($photo = data_get($record, $field)) {
                return filter_var($photo, FILTER_VALIDATE_URL) ? $photo : asset('storage/' . $photo);
            }
        }
        
        // Check employee/user relations
        if ($record->employee?->photo) {
            return asset('storage/' . $record->employee->photo);
        }
        
        if ($record->user?->photo) {
            return asset('storage/' . $record->user->photo);
        }
        
        // Generate initials avatar
        $name = '';
        if ($record->employee?->first_name) {
            $name = $record->employee->first_name . ' ' . $record->employee->last_name;
        } elseif ($record->first_name) {
            $name = $record->first_name . ' ' . $record->last_name;
        } elseif ($record->name) {
            $name = $record->name;
        }
        
        if ($name) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=4e73df&color=fff&size=128';
        }
        
        return null;
    }
    
    public function getImageUrl($record, $field = null)
    {
        if ($field && data_get($record, $field)) {
            $url = data_get($record, $field);
            return filter_var($url, FILTER_VALIDATE_URL) ? $url : asset('storage/' . $url);
        }
        
        return $this->getAvatarUrl($record, $field);
    }
    
    public function formatFieldValue($record, $field)
    {
        $value = data_get($record, $field);
        
        if (is_null($value)) {
            return 'N/A';
        }
        
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('M d, Y h:i A');
        }
        
        if (is_array($value)) {
            return implode(', ', array_filter($value));
        }
        
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }
        
        return $value;
    }
    
    public function getBadgeColor($value, $field = null)
    {
        $configService = app(\QuickerFaster\LaravelUI\Services\GUI\ViewConfigurationService::class);
        return $configService->getBadgeColor($value, $field);
    }
    
    public function getStatusBadgeClass($status)
    {
        $classes = [
            'active' => 'bg-success-soft text-success',
            'approved' => 'bg-success-soft text-success',
            'is_approved' => 'bg-success-soft text-success',
            'confirmed' => 'bg-success-soft text-success',
            'pending' => 'bg-warning-soft text-warning',
            'needs_review' => 'bg-danger-soft text-danger',
            'rejected' => 'bg-danger-soft text-danger',
            'cancelled' => 'bg-danger-soft text-danger',
            'inactive' => 'bg-secondary-soft text-secondary',
            'draft' => 'bg-secondary-soft text-secondary',
        ];
        
        return $classes[strtolower($status)] ?? 'bg-secondary-soft text-secondary';
    }
}