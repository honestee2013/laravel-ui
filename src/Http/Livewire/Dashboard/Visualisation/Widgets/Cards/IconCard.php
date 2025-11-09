<?php

namespace QuickerFaster\LaravelUI\Http\Livewire\Dashboard\Visualisation\Widgets\Cards;


use Livewire\Component;

use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Modules\System\Traits\DataTable\DataTableFieldsConfigTrait;
use App\Modules\Dashboard\Livewire\Visualisation\Widgets\Charts\Chart;
use QuickerFaster\LaravelUI\Http\Livewire\Dashboard\Visualisation\Widgets\AggregatorAbstractWidget;


class IconCard extends AggregatorAbstractWidget
{
    public $prefix = '';
    public $suffix = '';
    public $iconCSSClass = '';
    public $aggregationMethodTitle = 'total';
    public $showRecordNameOnly = false;




    public function updateInfo() {
        $aggregationData = $this->getAggregationData();
    }


    public function render()
    {
        return view('dashboard.views::components.visualisation.widgets.cards.icon-card');
    }

}
