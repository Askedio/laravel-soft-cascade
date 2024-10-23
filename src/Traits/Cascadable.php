<?php

namespace Askedio\SoftCascade\Traits;

trait Cascadable
{
    /**
     * Check if the model is enabled to cascade.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    protected function isCascadable($model): bool
    {
        return method_exists($model, 'getSoftCascade');
    }
}