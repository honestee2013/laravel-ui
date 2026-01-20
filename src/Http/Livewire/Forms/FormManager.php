<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Forms;

use Livewire\Component;
use QuickerFaster\LaravelUI\Http\Livewire\DataTables\DataTableManager;



class FormManager extends DataTableManager
{



    /*protected $listeners = [
    ];*/

    public function mount()
    {
        parent::mount();
    }


   /* public function render() {
        return view('system.views::forms.form-manager');
    }*/

    public function render()
    {

        $UIFramework = config('qf_laravel_ui.ui_framework', 'bootstrap'); // default to bootstrap
        $viewPath =  "qf::components.livewire.$UIFramework";
        return view("$viewPath.forms.form-manager");
    }


}
