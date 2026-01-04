<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Widgets\Cards;

use Livewire\Component;

class ActionCardWidget extends Component
{
    public $widgetId;
    public $config;
    public $isLoading = false;
    public $isProcessing = false;
    public $actionResult = null;
    
    protected $listeners = [
        'refreshWidget' => 'refresh'
    ];
    
    public function mount($widgetId, $config)
    {
        $this->widgetId = $widgetId;
        $this->config = $config;
    }
    
    public function triggerAction()
    {
        // Reset any previous result
        $this->actionResult = null;
        $this->isProcessing = true;
        
        // Check if action has confirmation
        if (isset($this->config['action']['confirm']) && $this->config['action']['confirm']) {
            $this->dispatch('showConfirmation', [
                'title' => $this->config['action']['confirm_title'] ?? 'Confirm Action',
                'message' => $this->config['action']['confirm_message'] ?? 'Are you sure you want to perform this action?',
                'widgetId' => $this->widgetId,
                'action' => 'executeAction'
            ]);
            $this->isProcessing = false;
            return;
        }
        
        $this->executeAction();
    }
    
    public function executeAction()
    {
        try {
            if (isset($this->config['action']['event'])) {
                // Emit custom event
                $this->dispatch($this->config['action']['event'], $this->widgetId);
                $this->actionResult = [
                    'type' => 'success',
                    'message' => $this->config['action']['success_message'] ?? 'Action triggered successfully'
                ];
                
            } elseif (isset($this->config['action']['url'])) {
                // Handle URL navigation
                if (isset($this->config['action']['target']) && $this->config['action']['target'] === '_blank') {
                    // Open in new tab
                    $this->dispatch('openNewTab', ['url' => $this->config['action']['url']]);
                } else {
                    // Navigate in same window
                    return redirect()->to($this->config['action']['url']);
                }
                
                $this->actionResult = [
                    'type' => 'info',
                    'message' => 'Redirecting...'
                ];
                
            } elseif (isset($this->config['action']['method'])) {
                // Execute custom method
                $method = $this->config['action']['method'];
                if (method_exists($this, $method)) {
                    $result = $this->$method();
                    $this->actionResult = [
                        'type' => 'success',
                        'message' => $result ?? 'Action completed'
                    ];
                }
            }
            
        } catch (\Exception $e) {
            $this->actionResult = [
                'type' => 'error',
                'message' => $this->config['action']['error_message'] ?? 'Action failed: ' . $e->getMessage()
            ];
        }
        
        $this->isProcessing = false;
        
        // Auto-clear success message after delay
        if ($this->actionResult && $this->actionResult['type'] === 'success') {
            $this->dispatch('clearActionResult', ['widgetId' => $this->widgetId]);
        }
    }
    
    public function refresh()
    {
        $this->isLoading = true;
        $this->dispatch('refreshDashboard');
    }
    
    public function clearResult()
    {
        $this->actionResult = null;
    }
    
    // Example custom method that can be called via action config
    public function quickApproveAll()
    {
        // This is just an example - implement your actual logic
        // sleep(1); // Simulate processing
        
        return "All items approved successfully!";
    }
    
    public function render()
    {
        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap');
        $layoutPath = "qf::components.livewire.$UIFramework.widgets";
        
        return view("$layoutPath.cards.action-card", [
            'isLoading' => $this->isLoading,
            'isProcessing' => $this->isProcessing,
            'actionResult' => $this->actionResult,
            'color' => $this->config['color'] ?? 'primary',
            'buttonSize' => $this->config['button_size'] ?? 'default',
            'buttonVariant' => $this->config['button_variant'] ?? 'filled',
        ]);
    }
}