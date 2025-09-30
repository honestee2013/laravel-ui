<?php


namespace QuickerFaster\LaravelUI\Http\Livewire\Dashboard\Visualisation\Widgets;

use Livewire\Component;
use App\Modules\Analytics\Data\Aggregator;


abstract class AggregatorAbstractWidget extends Component
{



    private $colorPalette = [
        '#FF5733', // Vibrant orange
        '#33FF57', // Bright green
        '#3357FF', // Deep blue
        '#FF33A1', // Hot pink
        '#33FFF5', // Aqua
        '#F5FF33', // Yellow
        '#FF8C00', // Dark orange
        '#800080', // Purple
        '#008080', // Teal
        '#000080', // Navy
    ];

    private $defaultTransparency = 0.6; // Adjust transparency level (0.0 to 1.0)

    public $recordTable;
    public $recordModel;
    public $recordName;
    public $column;
    public $groupBy = 'daily'; // Default grouping
    public $groupByTable = "";
    public $groupByTableColumn = "";
    public $aggregationMethod = "sum";

    // Query filters
    public $filters = [];

    // Time filters
    public $timeDuration = 'this_month'; // Default predefined duration
    public $fromTime; // Custom range start
    public $toTime;   // Custom range end

    // Aggregation values
    public $min = 0;
    public $showMin = true;
    public $ave = 0;
    public $showAve = true;
    public $max = 0;
    public $showMax = true;
    public $sum = 0;
    public $showSum = true;
    public $count = 0;
    public $showCount = true;

    public $valueChange = 0;
    public $valueChangePercent = 0;
    public $valueChangeTimeDuration;



    public ?string $pivotTable = null;
    public ?string $pivotModelColumn = null;
    public ?string $pivotRelatedColumn = null;
    public ?string $pivotModelType = null;
    public  $pivotRelatedColumnIn;


    public $title = "";
    




    protected $listeners = [
        'configChangedEvent' => 'updateConfig',
    ];



    public function mount()
    {
        $this->updateInfo();
    }

    public abstract function updateInfo();


    protected function getAggregationData()
    {
        if ($this->timeDuration === 'custom') {
            // Use defaults if custom range is not fully specified
            if (!$this->fromTime) {
                $this->fromTime = now()->startOfMonth()->toDateString(); // Default to start of the current month
            }

            if (!$this->toTime) {
                $this->toTime = now()->endOfMonth()->toDateString(); // Default to end of the current month
            }

            // Validate after applying defaults
            $this->validateTimeRange();
        }

        // Fetch data using the Aggregator or DataHelper
        $aggregator = new Aggregator();
        $aggregator = $this->setUpAggregatorParameters($aggregator);
        $timeRange = $this->getTimeRange($this->timeDuration);

        if ($timeRange) {
            $aggregator->setTimeRange($timeRange['from'], $timeRange['to']);
        }



        $aggregationData = $aggregator->fetch();
        $this->setUpAggregationValues($aggregationData["data"]);
        $this->setUpChangedValue($aggregationData["data"]);

        //dd($aggregationData, $this->recordModel, $this->filters);

        return $aggregationData;
    }

    protected function setUpAggregatorParameters($aggregator)
    {
        if ($aggregator) {

            if (isset($this->recordModel))
                $aggregator->setModel($this->recordModel);
            else
                $aggregator->setTable($this->recordTable);                


            $aggregator->setColumn($this->column)
                ->groupBy($this->groupBy)
                ->setGroupByTable($this->groupByTable)
                ->setGroupByTableColumn($this->groupByTableColumn)
                ->setAggregationMethod($this->aggregationMethod)
                ->setFilters($this->filters)
                ->setPivotJoin($this->pivotTable, $this->pivotModelColumn, $this->pivotRelatedColumn, $this->pivotModelType, $this->pivotRelatedColumnIn)
                ;

            if ($this->timeDuration === 'custom' && $this->fromTime && $this->toTime) {
                $aggregator->setTimeRange($this->fromTime, $this->toTime);
            } /*else {

           $timeRange = $this->getTimeRange($this->timeDuration);
           if ($timeRange) {
               $aggregator->setTimeRange($timeRange['from'], $timeRange['to']);
           }
       }*/
            $aggregator;
        }

        return $aggregator;
    }

    protected function setUpAggregationValues($data)
    {
        if ($data) {
            if ($this->showSum)
                $this->sum = array_sum($data);
            if ($this->showCount)
                $this->count = count($data);
            if ($this->showMax)
                $this->max = max($data);
            if ($this->showMin)
                $this->min = min($data);
            if ($this->showAve && count($data) != 0) // Avoid devision by 0
                $this->ave = round(array_sum($data) / count($data), 2);
        } else {
            $this->sum = $this->count = $this->max = $this->min = $this->ave = 0;
        }

    }


    protected function setUpChangedValue($data)
    {

        if ($data) {
            // Assuming the $this->timeDuration = "today"
            $otherTimeDuration = "yesterday";
            if ($this->timeDuration == "yesterday")
                $otherTimeDuration = "today";
            else if ($this->timeDuration == "this_week")
                $otherTimeDuration = "last_week";
            else if ($this->timeDuration == "last_week")
                $otherTimeDuration = "this_week";
            else if ($this->timeDuration == "this_month")
                $otherTimeDuration = "last_month";
            else if ($this->timeDuration == "last_month")
                $otherTimeDuration = "this_month";
            else if ($this->timeDuration == "this_year")
                $otherTimeDuration = "last_year";
            else if ($this->timeDuration == "last_year")
                $otherTimeDuration = "this_year";


            $otherAggregator = new Aggregator();
            $otherAggregator = $this->setUpAggregatorParameters($otherAggregator);

            $timeRange = $this->getTimeRange($otherTimeDuration);
            if ($timeRange) {
                $otherAggregator->setTimeRange($timeRange['from'], $timeRange['to']);
            }
            $otherAggregationData = $otherAggregator->fetch();

            // Defference should be between current time and the previous tmie eg. (this_week - last_week)
            if (str_contains($this->timeDuration, "this") || str_contains($this->timeDuration, "today") && array_sum($data)) {
                $this->valueChange = array_sum($data) - array_sum($otherAggregationData["data"]);

                $sum = array_sum($data);
                if ($sum != 0 && $this->valueChange)
                    $this->valueChangePercent = $this->valueChange / $sum * 100;
            } else if (array_sum($data)) {
                $this->valueChange = array_sum($otherAggregationData["data"]) - array_sum($data);

                $sum = array_sum($otherAggregationData["data"]);
                if ($sum != 0 && $this->valueChange)
                    $this->valueChangePercent = $this->valueChange / $sum * 100;
            }


            $this->valueChangePercent = intval(round($this->valueChangePercent));

            //dd($this->timeDuration, $this->valueChangePercent, $this->valueChange,  $data, $otherAggregationData["data"]);
        } else {
            $this->valueChangePercent = 0;
        }


        // To be used for label
        $this->valueChangeTimeDuration = "today";
        if (str_contains($this->timeDuration, "week"))
            $this->valueChangeTimeDuration = "this week";
        if (str_contains($this->timeDuration, "month"))
            $this->valueChangeTimeDuration = "this month";
        if (str_contains($this->timeDuration, "year"))
            $this->valueChangeTimeDuration = "this year";

    }


    protected function getTimeRange($duration)
    {
        switch ($duration) {
            case 'today':
                return [
                    'from' => now()->startOfDay()->toDateTimeString(),
                    'to' => now()->endOfDay()->toDateTimeString(),
                ];
            case 'yesterday':
                $yesterday = now()->copy()->subDay();
                return [
                    'from' => $yesterday->startOfDay()->toDateTimeString(),
                    'to' => $yesterday->endOfDay()->toDateTimeString(),
                ];
            case 'this_week':
                return [
                    'from' => now()->startOfWeek()->toDateTimeString(),
                    'to' => now()->endOfWeek()->toDateTimeString(),
                ];
            case 'last_week':
                $lastWeek = now()->copy()->subWeek();
                return [
                    'from' => $lastWeek->startOfWeek()->toDateTimeString(),
                    'to' => $lastWeek->endOfWeek()->toDateTimeString(),
                ];
            case 'this_month':
                $startOfMonth = now()->startOfMonth();
                $endOfMonth = now()->endOfMonth();
                return [
                    'from' => $startOfMonth->toDateTimeString(),
                    'to' => $endOfMonth->toDateTimeString(),
                ];
            case 'last_month':
                $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();
                $lastMonthEnd = now()->subMonthNoOverflow()->endOfMonth();
                return [
                    'from' => $lastMonthStart->toDateTimeString(),
                    'to' => $lastMonthEnd->toDateTimeString(),
                ];
            case 'this_year':
                return [
                    'from' => now()->startOfYear()->toDateTimeString(),
                    'to' => now()->endOfYear()->toDateTimeString(),
                ];
            case 'last_year':
                $lastYear = now()->subYear()->startOfYear();
                return [
                    'from' => $lastYear->toDateTimeString(),
                    'to' => $lastYear->endOfYear()->toDateTimeString(),
                ];
            default:
                return null;
        }
    }


    protected function validateTimeRange(): bool
    {
        if ($this->fromTime && $this->toTime) {
            if ($this->fromTime > $this->toTime) {
                $this->addError('fromTime', 'The "From" date must be earlier than the "To" date.');
                $this->addError('toTime', 'The "To" date must be later than the "From" date.');
                return false;
            } else {
                $this->resetErrorBag(['fromTime', 'toTime']);
                return true;
            }
        }

        return false;
    }


    public function updateConfig($data)
    {
        if (is_array($data) && !empty($data)) {
            if (array_key_exists("filters", $data)) {
                $this->updateFilters($data["filters"]);
            } else {
                $this->updateData($data);
            }
        }
    }


    protected function updateData($data)
    {
        if (is_array($data) && !empty($data)) {
            foreach ($data as $key => $value) {
                if (isset($this->$key))
                    $this->$key = $value;
            }
        }

        $this->updateInfo();
    }


    protected function updateFilters($data)
    {

        if (is_array($data) && !empty($data)) {
            foreach ($data as $key => $value) {
                if (isset($this->filters[$key]))
                    $this->filters[$key] = $value;
                else
                    $this->filters[$key] = $value;
            }
        }

        $this->updateInfo();
    }


    // Function to generate colors dynamically
    protected function generateColors($count)
    {
        $colors = [];
        $paletteSize = count($this->colorPalette);

        for ($i = 0; $i < $count; $i++) {
            $baseColor = $this->colorPalette[$i % $paletteSize];
            $rgbaColor = $this->hexToRgba($baseColor, $this->defaultTransparency);
            $colors[] = $rgbaColor;
        }

        return $colors;
    }

    // Helper function to convert HEX to RGBA
    protected function hexToRgba($hex, $transparency)
    {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba($r, $g, $b, $transparency)";
    }



    public function updatedGroupBy($value)
    {
        $aggregator = new Aggregator();
        if (isset($this->recordModel))
            $aggregator->setModel($this->recordModel);
        else
            $aggregator->setTable($this->recordTable);

        $aggregator->setColumn($this->column)
            ->groupBy($this->groupBy)
            ->setAggregationMethod($this->aggregationMethod)
            ->setFilters($this->filters);

        $aggregationData = $aggregator->fetch();
        $this->setUpChartData($aggregationData);

        // Emit an event to update the chart
        $this->updateInfo();
    }


    public function updatedTimeDuration()
    {
        if ($this->timeDuration !== 'custom') {
            $this->fromTime = null;
            $this->toTime = null;
            $this->resetErrorBag(['fromTime', 'toTime']); // Clear errors
        }

        $this->updateInfo();
    }

    public function updatedFromTime()
    {
        if ($this->validateTimeRange()) {
            $this->updateInfo();
        }
    }

    public function updatedToTime()
    {
        if ($this->validateTimeRange()) {
            $this->updateInfo();
        }
    }








}
