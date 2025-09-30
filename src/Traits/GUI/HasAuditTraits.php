<?php

namespace QuickerFaster\LaravelUI\Traits\GUI;



use App\Models\User;
use Illuminate\Support\Facades\Schema;

trait HasAuditTraits
{
    protected static $auditFields = [
        'created_by',
        'updated_by',
        'deleted_by',
        'approved_by',
        'cancelled_by',
        'rejected_by',
        'restored_by',
    ];

    public function __get($key)
    {
        // Automatically return username for audit fields
        if (in_array($key, static::$auditFields)) {
            return $this->getAuditUserName($key);
        }

        return parent::__get($key);
    }

    protected function getAuditUserName($field)
    {
        if ($this->hasAuditField($field)) {
            $userId = $this->getAttribute($field);
            return $userId ? optional(User::find($userId))->name : null;
        }
        return null;
    }

    protected function hasAuditField($field)
    {
        return Schema::hasColumn($this->getTable(), $field);
    }
}

