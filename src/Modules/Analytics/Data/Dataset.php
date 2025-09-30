<?php
namespace App\Modules\Analytics\Data;

class Dataset
{
    protected string $name;
    protected array $data = [];
    protected Aggregator $aggregator;

    public function __construct(string $name, Aggregator $aggregator)
    {
        $this->name = $name;
        $this->aggregator = $aggregator;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function fetch(): array
    {
        $result = $this->aggregator->fetch();
        $this->data = $this->transform($result);
        return $this->data;
    }

    protected function transform(array $result): array
    {
        // Example: Normalize data to percentages
        $total = array_sum($result['data']);
        if ($total > 0) {
            $result['data'] = array_map(fn($value) => round(($value / $total) * 100, 2), $result['data']);
        }
        return $result;
    }
}
