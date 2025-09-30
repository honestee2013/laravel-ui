

<x-dashboard.views::layouts.dashboards.default-dashboard>

    @php
        $statusCompletedId = App\Modules\Core\Models\Status::where("name", "completed")->first()?->id ?? 0;
        $statusInProgressId = App\Modules\Core\Models\Status::where("name", "in_progress")->first()?->id ?? 0;
        $selectedProcessId = $selectedProcessId ?? 0;
        $timeDurationLabel = ucfirst(str_replace('_', ' ', $timeDuration));
    @endphp



    <x-slot name="mainTitle">
        <strong class="text-info text-gradient">{{ $selectedProcessName?? ''}} </strong> Overview
    </x-slot>

    <x-slot name="subtitle">
        <span class="text-primary text-xs fst-italic">{{ $timeDuration !== 'custom' ? ucfirst(str_replace('_', ' ', $timeDuration)) : $fromTime . ' to ' . $toTime }}</span>
    </x-slot>

    <x-slot name="controls">
        <div class="row">
            <div class="input-group  w-90 col-sm-auto w-sm-auto  rounded-pill p-3 p-sm-0 ms-3 ">

                <select wire:model.live.500ms="timeDuration" id="time_duration"
                    class="form-select  rounded-pill p-1 ps-3  px-sm-3 m-1 small-control">
                    <option value="" disabled>Select Duration...</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="last_week">Last Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_year">This Year</option>
                    <option value="last_year">Last Year</option>
                    {{--<option value="custom">Custom Range...</option>--}}
                </select>
            </div>

            {{--<select wire:model.live.500ms="selectedProcessId" id="time_duration"
                class="col form-select  rounded-pill p-1 ps-3  px-sm-3 m-1 small-control">
                <option value="" disabled>Select Process...</option>
                @foreach (App\Modules\Production\Models\ProductionProcess::all() as $process )
                    <option value="{{$process->id}}">{{ucfirst($process->name)}}</option>
                @endforeach
            </select>--}}
        </div>
    </x-slot>


    {{------ DASHBOAD BODY CONTENT --------}}
    {{ $body?? '' }}


    {{---<div class="row g-4 mb-4">
        <div class="col-12 col-sm-3">
            <livewire:dashboard.visualisation.widgets.cards.icon-card
                recordModel="App\Modules\Production\Models\ProductionProcessLog"
                recordName="Pending"
                :filters="[
                    ['production_process_id', '=', $selectedProcessId],
                    ['status_id', '=', App\Modules\Core\Models\Status::getStatusId('Pending')]
                ]"
                showRecordNameOnly="true"
                column="id"
                groupBy="id"
                aggregationMethod="count"
                :timeDuration="$timeDuration"
                iconCSSClass="fas fa-hourglass-start text-lg opacity-10"
                key="icon-card-1"

            />
        </div>
        <div class="col-12 col-sm-3">
            <livewire:dashboard.visualisation.widgets.cards.icon-card
                recordModel="App\Modules\Production\Models\ProductionProcessLog"
                recordName="In Progress"
                :filters="[
                    ['production_process_id', '=', $selectedProcessId],
                    ['status_id', '=', App\Modules\Core\Models\Status::getStatusId('in_progress')]
                ]"
                showRecordNameOnly="true"
                column="id"
                groupBy="id"
                aggregationMethod="count"
                :timeDuration="$timeDuration"
                iconCSSClass="fas fa-sync-alt fa-spin text-lg opacity-10"
                key="icon-card-2"
            />
        </div>
        <div class="col-12 col-sm-3">
            <livewire:dashboard.visualisation.widgets.cards.icon-card
                recordModel="App\Modules\Production\Models\ProductionProcessLog"
                recordName="Completed"
                :filters="[
                    ['production_process_id', '=', $selectedProcessId],
                    ['status_id', '=', App\Modules\Core\Models\Status::getStatusId('completed')]
                ]"
                showRecordNameOnly="true"
                column="id"
                groupBy="id"
                aggregationMethod="count"
                :timeDuration="$timeDuration"
                iconCSSClass="fas fa-check-double text-lg opacity-10"
                key="icon-card-3"
            />
        </div>
        <div class="col-12 col-sm-3">
            <livewire:dashboard.visualisation.widgets.cards.icon-card
                recordModel='App\Modules\Production\Models\ProductionProcessDowntime'
                chartType="line"
                recordName='Downtime (Min)'
                showRecordNameOnly='true'
                column='duration'
                aggregationMethod='sum'
                :timeDuration='$timeDuration'

                :filters="[
                    ['production_process_log_id', '=', $selectedProcessLogId],
                ]"

                canvasHeight=100
                :controls="['chartType']"
                iconCSSClass='fas fa-exclamation-triangle text-lg opacity-10'
                key="icon-card-4"
            />
        </div>
    </div>

    <livewire:dashboard.visualisation.widgets.progresses.progress-bar
        :progress="30"
        :elementLabel="$timeDurationLabel"
        progressLabel="Overall Progress"
        color="success"
    />

    <div class="row g-5 mb-4">
        <div class="col-12 col-sm-6" >
            <livewire:dashboard.visualisation.widgets.charts.chart
                recordModel='App\Modules\Production\Models\ProductionProcessInput'
                chartType="bar"
                recordName='Consumed Resources'
                showRecordNameOnly='true'
                column='actual_quantity'

                groupBy="item_id"
                groupByTable="items"
                groupByTableColumn="name"

                aggregationMethod='sum'
                :timeDuration='$timeDuration'

                :filters="[
                    ['production_process_log_id', '=', $selectedProcessLogId],
                ]"

                canvasHeight=100
                :controls="['chartType']"
                key="chart1"

            />
        </div>
        <div class="col-12 col-sm-6" >
            <livewire:dashboard.visualisation.widgets.charts.chart
                recordModel="App\Modules\Production\Models\ProductionProcessOutput"
                chartType="bar"
                recordName="Output Products"
                showRecordNameOnly="true"
                column="actual_quantity"
                groupBy="item_id"
                groupByTable="items"
                groupByTableColumn="name"
                aggregationMethod="sum"
                :timeDuration="$timeDuration"
                :filters="[
                    ['production_process_log_id', '=', $selectedProcessLogId],
                ]"
                canvasHeight=100
                :controls="['chartType']"
                key="chart2"
            />
        </div>
    </div>


    <div class="col-12 col-sm-9" >
        <livewire:dashboard.visualisation.widgets.charts.chart
            recordModel='App\Modules\Production\Models\ProductionProcessDowntime'
            chartType="line"
            recordName='Downtime (Min)'
            showRecordNameOnly='true'
            column='duration'
            aggregationMethod='sum'
            :timeDuration='$timeDuration'

            :filters="[
                ['production_process_log_id', '=', $selectedProcessLogId],
            ]"

            canvasHeight=100
            :controls="['chartType']"
            key="chart3"
        />
    </div>--}}







    {{---

    <livewire:dashboard.visualisation.widgets.counters.count-up count-to="23980" />

    @php
        $duration = "+10 year";
    @endphp
    <livewire:dashboard.visualisation.widgets.counters.count-down :end-time="strtotime($duration)" />


    <livewire:dashboard.visualisation.widgets.steppers.stepper-wizard
        :steps="[
            ['label' => 'Step 1', 'content' => '<p>Content for Step 1</p>'],
            ['label' => 'Step 2', 'content' => '<p>Content for Step 2</p>'],
            ['label' => 'Step 3', 'content' => '<p>Content for Step 3</p>'],
            ['label' => 'Step 4', 'content' => '<p>Content for Step 3</p>'],
            ['label' => 'Step 5', 'content' => '<p>Content for Step 3</p>'],
        ]"
    />


--}}

</x-dashboard.views::layouts.dashboards.default-dashboard>









































