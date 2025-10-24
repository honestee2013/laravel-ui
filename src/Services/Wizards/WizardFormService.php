<?php

namespace QuickerFaster\LaravelUI\Services\Wizards;

use QuickerFaster\LaravelUI\Services\DataTables\DataTableFormService;
use Illuminate\Support\Facades\DB;

class WizardFormService extends DataTableFormService
{
    /**
     * Save all wizard steps in a single transaction
     */
    public function saveWizardSteps($wizardConfig, $formData, $userId = null)
    {
        return DB::transaction(function () use ($wizardConfig, $formData, $userId) {
            $savedRecords = [];
            
            foreach ($wizardConfig['steps'] as $step) {
                if (empty($step['model']) || empty($formData[$step['model']])) {
                    continue;
                }
                
                $modelClass = $step['model'];
                $modelData = $formData[$step['model']];
                
                // Add audit trail fields
                $modelData = $this->addAuditTrailFields($modelData, 'created', $wizardConfig);
                
                // Hash passwords if any
                $modelData = $this->hashPasswordFields($modelData, false, null);
                
                // Create record
                $record = $modelClass::create($modelData);
                $savedRecords[$step['model']] = $record;
            }
            
            return $savedRecords;
        });
    }
}