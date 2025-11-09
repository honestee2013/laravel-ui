<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Dashboard\Visualisation\Widgets\Counters;


use Livewire\Component;

use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Modules\System\Traits\DataTable\DataTableFieldsConfigTrait;



class CountUp extends Component
{

    protected $listeners = [
        'recordSavedEvent' => '$refresh',
    ];



    public $countTo;
    public $id = 1;
    public $prefix = "$";
    public $suffix = "";
    public $useGrouping = "true";
    public $groupingSeparator = ",";

    public function mount($countTo = 0)
    {
        $this->countTo = $countTo;
    }


public function increase() {

    $this->countTo += 1000;
    $this->dispatch('update-count-up');
}


    public function render()
    {
        return view('dashboard.views::components.visualisation.widgets.counters.count-up');

    }

}
