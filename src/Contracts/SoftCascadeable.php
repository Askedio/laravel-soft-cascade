<?php

namespace Askedio\SoftCascade\Contracts;

interface SoftCascadeable
{
    /**
     * Cascade over Eloquent items.
     *
     * @param Illuminate\Database\Eloquent\Model $models
     * @param string                             $direction     update|delete|restore
     * @param array                              $directionData
     *
     * @return void
     */
    public function cascade($models, $direction, array $directionData = []);
}
