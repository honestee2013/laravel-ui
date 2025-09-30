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
        return view('core.views::forms.form-manager');
    }*/

    public function render()
    {
        return view('core.views::forms.form-manager', []);
    }


}
