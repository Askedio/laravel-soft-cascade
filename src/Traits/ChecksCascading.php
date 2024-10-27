<?php

namespace Askedio\SoftCascade\Traits;

trait ChecksCascading
{
    /**
     * Check if the model is enabled to cascade.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return ($model is object{ getSoftCascade: callable } ? true : false)
     */
    protected function hasCascadingRelations($model): bool
    {
        return method_exists($model, 'getSoftCascade');
    }
}
