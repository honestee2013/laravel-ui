<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Dashboard\Visualisation\Widgets\Cards;


use Livewire\Component;

use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Modules\Core\Traits\DataTable\DataTableFieldsConfigTrait;



class ImageCard extends Component
{

    protected $listeners = [
        'recordSavedEvent' => '$refresh',
    ];



    public function mount()
    {

    }


    public function render()
    {
         //return view('dashboard.views::components.visualisation.cards.live-card');


    }

}
