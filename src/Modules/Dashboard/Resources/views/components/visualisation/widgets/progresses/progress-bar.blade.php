<div class="my-3">

    <div class="progress-wrapper" >
        <div class="progress-info">
            <div class="progress-percentage">
                <span class="text-sm font-weight-normal">{{ $elementLabel?? '' }}</span>
            </div>
        </div>


        <div class="rounded-pill progress" style="height: auto" >
            <div class="rounded-pill my-auto  progress-bar bg-gradient-{{ $this->getProgressColor() }}" role="progressbar" style="width: {{ $progress }}%; {{$progressBarCSS}}; "
                aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                <span  style="{{$progressLabelCSS}}"> {{ $progressLabel?? '' }} {{  $showPercentage? $progress.'%': '' }}</span>
            </div>
        </div>
    </div>


    {{--<div class="mt-5"> FOR TESTING PORPOSES
        <button class="btn btn-primary btn-sm" wire:click="incrementProgress">Increase</button>
        <button class="btn btn-danger btn-sm" wire:click="decrementProgress">Decrease</button>
    </div>--}}
</div>
