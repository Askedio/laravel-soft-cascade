<?php

namespace Askedio\SoftCascade\Exceptions;

use Illuminate\Support\Arr;
use RuntimeException;

/**
 * @template TModelString of class-string<\Illuminate\Database\Eloquent\Model>
 */
class SoftCascadeRestrictedException extends RuntimeException
{
    /**
     * Name of the affected Eloquent model.
     *
     * @var TModelString
     */
    protected $model;

    /**
     * The affected model foreignKey.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The affected model foreignKeyIds.
     *
     * @var int[]
     */
    protected $foreignKeyIds;

    /**
     * Set the affected Eloquent model and instance foreignKeyIds.
     *
     * @param TModelString $model
     * @param string       $foreignKey
     * @param int | int[]    $foreignKeyIds
     *
     * @return $this
     */
    public function setModel($model, $foreignKey, $foreignKeyIds)
    {
        $this->model = $model;
        $this->foreignKey = $foreignKey;
        $this->foreignKeyIds = Arr::wrap($foreignKeyIds);

        $this->message = "Integrity constraint violation [{$model}] where $foreignKey in (".implode(', ', $foreignKeyIds).')';

        return $this;
    }

    /**
     * Get the affected Eloquent model.
     *
     * @return TModelString
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the affected Eloquent model foreignKey.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Get the affected Eloquent model foreignKeyIds.
     *
     * @return int[]
     */
    public function getForeignKeyIds()
    {
        return $this->foreignKeyIds;
    }
}
