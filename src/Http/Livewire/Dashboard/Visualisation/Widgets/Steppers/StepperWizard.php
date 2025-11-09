<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Dashboard\Visualisation\Widgets\Steppers;


use Livewire\Component;

use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Modules\System\Traits\DataTable\DataTableFieldsConfigTrait;



class StepperWizard extends Component
{

    protected $listeners = [
        'recordSavedEvent' => '$refresh',
    ];



    public $steps;         // Array of steps
    public $currentStep;   // Current step index

    public function mount($steps = [])
    {
        $this->steps = $steps;
        $this->currentStep = 0; // Start at the first step
    }

    public function goToNextStep()
    {
        if ($this->currentStep < count($this->steps) - 1) {
            $this->currentStep++;
        }
    }

    public function goToPreviousStep()
    {
        if ($this->currentStep > 0) {
            $this->currentStep--;
        }
    }



    public function render()
    {
        return view('dashboard.views::components.visualisation.widgets.steppers.stepper-wizard');

    }

}
