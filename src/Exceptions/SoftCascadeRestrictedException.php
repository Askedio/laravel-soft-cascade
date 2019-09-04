<?php

namespace Askedio\SoftCascade\Exceptions;

use Illuminate\Support\Arr;
use RuntimeException;

class SoftCascadeRestrictedException extends RuntimeException
{
    /**
     * Name of the affected Eloquent model.
     *
     * @var string
     */
    protected $model;

    /**
     * The affected model foreignKey.
     *
     * @var int|array
     */
    protected $foreignKey;

    /**
     * The affected model foreignKeyIds.
     *
     * @var int|array
     */
    protected $foreignKeyIds;

    /**
     * Set the affected Eloquent model and instance foreignKeyIds.
     *
     * @param string    $model
     * @param string    $foreignKey
     * @param int|array $foreignKeyIds
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
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the affected Eloquent model foreignKeyIds.
     *
     * @return int|array
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Get the affected Eloquent model foreignKeyIds.
     *
     * @return int|array
     */
    public function getForeignKeyIds()
    {
        return $this->foreignKeyIds;
    }
}
