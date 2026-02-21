<?php

namespace Askedio\SoftCascade\Contracts;

interface SoftCascadeable
{
    /**
     * Cascade over Eloquent items.
     *
     * @param \Illuminate\Database\Eloquent\Model $models
     * @param 'update'|'delete'|'restore'         $direction
     * @param array                               $directionData
     *
     * @return void
     */
    public function cascade($models, $direction, array $directionData = []);
}
