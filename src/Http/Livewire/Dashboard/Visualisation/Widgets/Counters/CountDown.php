<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Dashboard\Visualisation\Widgets\Counters;


use Livewire\Component;

use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Modules\System\Traits\DataTable\DataTableFieldsConfigTrait;



class CountDown extends Component
{

    protected $listeners = [
        'recordSavedEvent' => '$refresh',
    ];



    public $endTime; // Timestamp of when the countdown ends
    public $id = 1;

    public function mount($endTime)
    {
        $this->endTime = $endTime;
    }



    public function render()
    {
        return view('dashboard.views::components.visualisation.widgets.counters.count-down');
    }

}
