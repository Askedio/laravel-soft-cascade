<?php

namespace Askedio\SoftCascade\Contracts;

/**
 * TO-DO:
 * - Support for ON CASCADE SET NULL
 * - Support for ON CASCADE RESTRICT.
 */
interface SoftCascadeable
{
    /**
     * Cascade over Eloquent items.
     *
     * @param Illuminate\Database\Eloquent\Model $models
     * @param string                             $direction update|delete|restore
     * @param array                              $directionData
     *
     * @return void
     */
    public function cascade($models, $direction, $directionData = []);
}
