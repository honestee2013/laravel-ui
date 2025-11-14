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