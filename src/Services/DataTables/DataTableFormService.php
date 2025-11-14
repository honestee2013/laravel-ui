<?php

namespace QuickerFaster\LaravelUI\Services\DataTables;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use QuickerFaster\LaravelUI\Facades\DataTables\DataTableConfig;

class DataTableFormService
{
    /*public function handleUploadedImages($record, $fields)
    {
        
        // Alternatively set the supported image columns in config/datatable.php
        // foreach (config('datatable.supported_image_columns', []) as $imageField) {
        foreach (DataTableConfig::getSupportedImageColumnNames() as $imageField ) {
            if (isset($fields[$imageField]) && is_object($fields[$imageField])) {
                if (!$fields[$imageField]->isValid()) {
                    throw new \Exception('Invalid file upload.');
                }
                
                if (isset($record->{$imageField}) && is_string($record->{$imageField})) {
                    Storage::disk('public')->delete($record->{$imageField});
                }
        
                $path = $fields[$imageField]->store('uploads', 'public');
                $fields[$imageField] = $path;


            }
        }
        
        return $fields;
    }*/











public function handleUploadedFiles($record, $fields)
{
    // Handle images
    foreach (DataTableConfig::getSupportedImageColumnNames() as $field) {
        if (isset($fields[$field]) && $this->isUploadedFile($fields[$field])) {
            $fields[$field] = $this->processFileUpload($record, $fields[$field], $field, 'image');
        }
    }

    // Handle documents
    foreach (DataTableConfig::getSupportedDocumentColumnNames() as $field) {
        if (isset($fields[$field]) && $this->isUploadedFile($fields[$field])) {
            $fields[$field] = $this->processFileUpload($record, $fields[$field], $field, 'document');
        }
    }

    return $fields;
}

protected function isUploadedFile($value): bool
{
    // Check if it's a Livewire uploaded file or regular uploaded file
    if (is_object($value)) {
        // For Livewire uploaded files
        if (method_exists($value, 'getClientOriginalName')) {
            return true;
        }
        // For regular uploaded files
        if (method_exists($value, 'isValid')) {
            return $value->isValid();
        }
    }
    return false;
}

protected function processFileUpload($record, $file, string $fieldName, string $type): string
{
    // Validate file
    $this->validateFile($file, $type);

    // Delete old file if exists
    if (isset($record->{$fieldName}) && is_string($record->{$fieldName})) {
        Storage::disk('public')->delete($record->{$fieldName});
    }

    // Store in appropriate folder
    $folder = $type === 'image' ? 'uploads/images' : 'uploads/documents';
    
    // Get the original filename with extension
    $filename = $file->getClientOriginalName();
    $extension = $file->getClientOriginalExtension();
    
    // Generate unique filename
    $uniqueFilename = uniqid() . '_' . $filename;
    
    // Store the file
    $path = $file->storeAs($folder, $uniqueFilename, 'public');
    
    return $path;
}

protected function validateFile($file, string $type): void
{
    // Get original extension
    $extension = strtolower($file->getClientOriginalExtension());
    
    $allowed = $type === 'image' 
        ? ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg']
        : ['pdf', 'docx', 'doc', 'xlsx', 'xls', 'csv', 'txt'];

    if (!in_array($extension, $allowed)) {
        throw new \Exception("Unsupported file type: .{$extension}. Allowed: " . implode(', ', $allowed));
    }

    // Limit file size (e.g., 10MB)
    if ($file->getSize() > 10 * 1024 * 1024) {
        throw new \Exception('File too large. Max 10MB allowed.');
    }
}














    public function hashPasswordFields($fields, $isEditMode = false, $existingRecord = null)
    {
        foreach ($fields as $key => $value) {
            if (str_contains($key, 'password')) {
                if ($isEditMode && $existingRecord) {
                    if ($value && !Hash::check($value, $existingRecord->$key)) {
                        $fields[$key] = Hash::make($value);
                    } else {
                        unset($fields[$key]); // Remove unchanged password
                    }
                } else if ($value) {
                    $fields[$key] = Hash::make($value);
                } else {
                    unset($fields[$key]); // Remove empty password
                }
            }
        }
        
        return $fields;
    }

    public function addAuditTrailFields($fields, $action, $config)
    {
        if (!isset($config["auditTrail"]) || !in_array($action, $config["auditTrail"])) {
            return $fields;
        }
        
        $actorField = $action . "_by";
        $actionTimeField = $action . "_at";
        
        return array_merge($fields, [
            $actorField => auth()->id(),
            $actionTimeField => now()
        ]);
    }
}