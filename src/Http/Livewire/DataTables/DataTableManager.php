<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\DataTables;


use Livewire\Component;
use Illuminate\Support\Facades\Log;
use QuickerFaster\LaravelUI\Services\DataTables\DataTableManagerService;
use QuickerFaster\LaravelUI\Traits\DataTable\DataTableControlsTrait;

class DataTableManager extends Component
{
    use DataTableControlsTrait;

    // Component properties
    public $configFileName;
    public $config;
    public $modelName;
    public $recordName;
    public $moduleName;
    public $model;
    public $controls;
    public $columns;
    public $visibleColumns;
    public $fieldDefinitions;
    public $fieldGroups;
    public $multiSelectFormFields;
    public $singleSelectFormFields;
    public $simpleActions;
    public $moreActions;
    public $hiddenFields;
    public $readOnlyFields;
    public $isEditMode = false;
    public $selectedItem;
    public $sortField = 'id';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $modalCount = 0;
    public $refreshModalCount = 20;
    public $modalStack = [];
    public $modalCache = [];
    public $feedbackMessages = "";
    public $pageTitle;
    public $queryFilters = [];
    public $modalId = 'addEditModal';

    protected $listeners = [
        "setFeedbackMessageEvent" => "setFeedbackMessage",
        "changeFormModeEvent" => "changeFormMode",
        "changeSelectedItemEvent" => "changeSelectedItem",
        "openAddRelationshipItemModalEvent" => "openAddRelationshipItemModal",
        'checkPageRefreshTimeEvent' => 'checkPageRefreshTime',
        'addModalFormComponentStackEvent' => 'addModalFormComponentStack',
        'closeModalEvent' => 'closeModal',
    ];

    protected $dataTableManagerService;

    public function boot(DataTableManagerService $dataTableManagerService)
    {
        $this->dataTableManagerService = $dataTableManagerService;
    }

    public function mount()
    {
        Log::info("DataTableManager->mount(): " . $this->getId());
        $this->initializeComponent();
    }

    protected function initializeComponent()
    {
        $this->feedbackMessages = "";
        
        // Load configuration
        $configData = $this->dataTableManagerService->loadConfiguration(
            $this->model, 
            $this->moduleName, 
            $this->modelName, 
            $this->hiddenFields, 
            $this->readOnlyFields
        );
        
        // Set component properties from configuration
        foreach ($configData as $key => $value) {
            $this->$key = $value;
        }
        
        if (!$this->recordName) {
            $this->recordName = $this->modelName;
        }
        
        if (empty($this->readOnlyFields)) {
            $this->readOnlyFields = [];
        }
        
        Log::info($this->dataTableManagerService->getInlinableModels($this->fieldDefinitions));
    }

    public function addModalFormComponentStack($data)
    {
        if (isset($data['componentId'], $data['modalId'])) {
            $this->modalStack[$data['modalId']] = $data['componentId'];
            Log::info(json_encode($this->modalStack));
        }
    }

    public function closeModal($data)
    {
        if (!isset($data["modalId"])) {
            return;
        }
        
        $componentId = $this->modalStack[$data["modalId"]] ?? null;
        Log::info(json_encode($this->modalStack));
        
        $this->dispatch("close-modal-event", [
            "modalId" => $data["modalId"],
            "componentId" => $componentId,
            "componentIds" => array_values($this->modalStack),
        ]);
    }

    public function changeFormMode($data)
    {
        $this->isEditMode = ($data['mode'] === 'edit');
    }

    public function changeSelectedItem($id)
    {
        $this->selectedItem = $this->model::findOrFail($id);
    }

    public function openAddRelationshipItemModal($model, $moduleName = "", $recordId = null)
    {
        Log::info("Opening Add Relationship Item Modal. Model: {$model}, Module: {$moduleName}");
        $this->checkPageRefreshTime();
        
        $modalId = ++$this->modalCount;
        $modelName = class_basename($model);
        
        if (!$moduleName) {
            $moduleName = DataTableConfig::extractModuleNameFromModel($model);
        }
        
        // This would be better handled by a dedicated modal service
        $this->dispatchBrowserEvent('open-relationship-modal', [
            'model' => $model,
            'moduleName' => $moduleName,
            'modelName' => $modelName,
            'modalId' => $modalId,
        ]);
    }

    public function checkPageRefreshTime()
    {
        if ($this->modalCount >= $this->refreshModalCount) {
            $this->dispatch('confirm-page-refresh');
        }
    }

    

    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap

        $viewPath =  "qf::components.livewire.$UIFramework";
        return view("$viewPath.data-tables.data-table-manager")
            ->layout("$viewPath.layouts.app"); // 👈 important
    }
}