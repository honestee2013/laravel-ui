<?php

namespace QuickerFaster\LaravelUI\Traits\GUI;


use QuickerFaster\LaravelUI\Services\CodeGenerators\CodeGeneratorService;


trait HasAutoGenerateFieldTrait
{
    public function generateCodeForField(string $model, string $modelName, string $field): void
    {
        $code = CodeGeneratorService::generateCode($model, $modelName, $field);

        // Flat structure (DataTableForm)
        if (isset($this->fields) && is_array($this->fields)) {
            if (empty($this->fields[$field])) {
                $this->fields[$field] = $code;
            }
            return;
        }

        // Nested structure (WizardManager)
        if (isset($this->formData) && is_array($this->formData)) {
            // Try to get alias — expect the component to have getModelAlias or similar
            if (method_exists($this, 'getModelAlias')) {
                $alias = $this->getModelAlias($model);
            } elseif (method_exists($this, 'resolveModelAlias')) {
                $alias = $this->resolveModelAlias($model);
            } else {
                throw new \BadMethodCallException(
                    'Component using HasAutoGenerateFieldTrait must define getModelAlias() or resolveModelAlias().'
                );
            }

            if ($alias && isset($this->formData[$alias])) {
                if (empty($this->formData[$alias][$field] ?? null)) {
                    $this->formData[$alias][$field] = $code;
                }
                return;
            }
        }

        throw new \LogicException(
            'Auto-generate failed: component must have $fields (flat) or $formData (nested) and a model alias resolver.'
        );
    }
}

