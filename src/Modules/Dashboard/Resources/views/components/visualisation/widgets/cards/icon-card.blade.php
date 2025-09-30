<div class="card w-100">
    <div class="card-body p-3">
      <div class="row">
        <div class="col-8">
          <div class="numbers">
            <p class="text-sm mb-0 text-capitalize font-weight-bold">
                @if (!$showRecordNameOnly)
                    {{ str_replace("_", " ", ucfirst($this->timeDuration)) }}'s
                    {{ $aggregationMethodTitle?? ucfirst($aggregationMethod) }}
                @endif
                {{$recordName}}
            </p>

            <h5 class="font-weight-bolder mb-0">
                {{--- Aggregation value ---}}
                @if ($aggregationMethod == "average")
                    {{$prefix}}{{$ave}}{{$suffix}}
                @elseif ($aggregationMethod == "max")
                    {{$prefix}}{{$max}}{{$suffix}}
                @elseif ($aggregationMethod == "min")
                    {{$prefix}}{{$min}}{{$suffix}}
                @elseif ($aggregationMethod == "count")
                    {{$prefix}}{{$count}}{{$suffix}}
                @else {{--- SUM = TOTAL ---}}
                    {{$prefix}}{{$sum}}{{$suffix}}
                @endif

                {{--- Increase/Decrease Indcator for current duration ---}}
                @if (str_contains($timeDuration, "this"))
                    @if($valueChange > 0 && $timeDuration != 'custom')
                        {{--<i class="fa fa-arrow-up text-success"></i>--}}
                        <span class="text-success text-sm font-weight-bolder">+{{ abs($valueChangePercent) }}%</span>
                    @elseif($valueChange < 0 && $timeDuration != 'custom')
                        {{--<i class="fa fa-arrow-down text-danger"></i>--}}
                        <span class="text-danger text-sm font-weight-bolder">-{{ abs($valueChangePercent) }}%</span>
                    @endif
                    {{-- @elseif($this->valueChange < 0 && $timeDuration == 'custom') --}}
                @endif

            </h5>

          </div>
        </div>

        <div class="col-4 text-end">
          <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
            <i class="{{$iconCSSClass}}" aria-hidden="true"></i>
          </div>
        </div>

      </div>
    </div>
  </div>


