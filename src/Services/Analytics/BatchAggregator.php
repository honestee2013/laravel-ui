<?php

namespace QuickerFaster\LaravelUI\Services\Analytics;

class BatchAggregator
{
    protected array $queries = [];
    protected array $results = [];

    public function addQuery(string $key, Aggregator $aggregator): self
    {
        $this->queries[$key] = $aggregator;
        return $this;
    }

    public function addQueries(array $queries): self
    {
        foreach ($queries as $key => $aggregator) {
            $this->addQuery($key, $aggregator);
        }
        return $this;
    }

    public function execute(): array
    {
        $this->results = [];
        
        foreach ($this->queries as $key => $aggregator) {
            try {
                $this->results[$key] = $aggregator->fetch();
                
                // Add aggregations for all results (both static and database)
                $this->results[$key]['aggregations'] = $this->calculateAggregations(
                    $this->results[$key]['data'] ?? []
                );
                
            } catch (\Exception $e) {
                // If any query fails, return empty data for that widget
                $this->results[$key] = [
                    'data' => [0],
                    'labels' => ['Error'],
                    'aggregations' => $this->calculateAggregations([0])
                ];
                
                \Log::error("BatchAggregator failed for widget {$key}: " . $e->getMessage());
            }
        }
        
        return $this->results;
    }

    protected function calculateAggregations($data)
    {
        if (empty($data)) {
            return [
                'count' => 0,
                'sum' => 0,
                'average' => 0,
                'max' => 0,
                'min' => 0
            ];
        }

        return [
            'count' => count($data),
            'sum' => array_sum($data),
            'average' => count($data) > 0 ? round(array_sum($data) / count($data), 2) : 0,
            'max' => max($data),
            'min' => min($data)
        ];
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getResult(string $key): ?array
    {
        return $this->results[$key] ?? null;
    }
}