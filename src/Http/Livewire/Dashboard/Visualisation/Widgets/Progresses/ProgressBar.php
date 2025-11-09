<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Dashboard\Visualisation\Widgets\Progresses;


use Livewire\Component;

use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Modules\System\Traits\DataTable\DataTableFieldsConfigTrait;



class ProgressBar extends Component
{



    public $progress = 0; // Progress value (0–100)
    public $elementLabel;    // Label for the progress bar
    public $progressLabel;    // Label for the progress bar
    public $color = 'primary';    // Bootstrap color class (e.g., primary, success)
    public $showPercentage = true;    // Show the percentage value
    public $progressColors = [
        '25' => 'danger',
        '50' => 'warning',
        '75' => 'info',
        '100' => 'success',
    ];

    public $progressBarCSS = "height: 1em;";
    public $progressLabelCSS = "font-size: 0.88em;";


    public function mount()
    {

    }

    public function getProgressColor() {
        $color = 'danger';
        foreach ($this->progressColors as $key => $value) {
            if ($this->progress >= $key) {
                $color = $value;
            }
        }
        return $color;
    }

    /* For testing purposes */
    /*public function incrementProgress($amount = 10)
    {
        $this->progress = min(100, $this->progress + $amount);
    }

    public function decrementProgress($amount = 10)
    {
        $this->progress = max(0, $this->progress - $amount);
    }*/


    public function render()
    {
        return view('dashboard.views::components.visualisation.widgets.progresses.progress-bar');

    }

}
