<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Wizards;


use Livewire\Component;

class WizardStepForm extends Component
{
    public $fieldGroups;
    public $fieldDefinitions;
    public $formData;
    public $model;
    public $modelName;
    public $multiSelectFormFields;
    public $singleSelectFormFields;
    public $readOnlyFields;
    public $currentModelAlias;



    public function render()
    {

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap

        $viewPath =  "qf::components.livewire.$UIFramework";
        return view("$viewPath.wizards.wizard-step-form")
            ->layout("$viewPath.layouts.app"); // 👈 important


    }




    public function shouldDisplayFieldInWizard($field)
    {
        return true; // Wizards show all fields
    }
}