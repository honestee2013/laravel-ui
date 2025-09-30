<?php
namespace QuickerFaster\LaravelUI\Services;


class ConfigManager
{
    public static function getOptions($modelOrCallback, $displayField = 'name', $keyField = 'id')
    {
        if (is_callable($modelOrCallback)) {
            return call_user_func($modelOrCallback);
        }

        if (is_array($modelOrCallback)) {
            return $modelOrCallback;
        }

        if (!class_exists($modelOrCallback)) {
            throw new \InvalidArgumentException("Model class {$modelOrCallback} does not exist.");
        }

        return $modelOrCallback::pluck($displayField, $keyField)->toArray();
    }

}
