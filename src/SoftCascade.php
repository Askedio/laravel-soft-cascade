<?php

namespace Askedio\SoftCascade;

use Askedio\SoftCascade\Contracts\SoftCascadeable;
use Illuminate\Support\Str;

/**
 * TO-DO:
 * - Support for ON CASCADE SET NULL
 * - Support for ON CASCADE RESTRICT.
 */
class SoftCascade implements SoftCascadeable
{
    protected $direction;
    protected $directionData;

    /**
     * Cascade over Eloquent items.
     *
     * @param Illuminate\Database\Eloquent\Model $models
     * @param string                             $direction update|delete|restore
     * @param array                              $directionData
     *
     * @return void
     */
    public function cascade($models, $direction, $directionData = [])
    {
        $this->direction = $direction;
        $this->directionData = $directionData;
        $this->run($models);
    }

    /**
     * Run the cascade.
     *
     * @param Illuminate\Database\Eloquent\Model $models
     *
     * @return void
     */
    protected function run($models)
    {
        $models = collect($models);
        if ($models->count() > 0) {
            $model = $models->first();

            if (!is_object($model)) {
                return;
            }

            if (!$this->isCascadable($model)) {
                return;
            }

            $this->relations($model, $model->getForeignKey(), $models->pluck($model->getKeyName()));
        }
        return;
    }

    /**
     * Iterate over the relations.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     * @param string                             $foreignKey
     * @param array                              $foreignKeyIds
     *
     * @return mixed
     */
    protected function relations($model, $foreignKey, $foreignKeyIds)
    {
        $relations = $model->getSoftCascade();

        if (empty($relations)) {
            return;
        }

        foreach ($relations as $relation) {
            $this->validateRelation($model, $relation);
            $this->execute($model->$relation(), $foreignKey, $foreignKeyIds);
        }
    }

    /**
     * Execute delete, or restore.
     *
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     * @param string                                          $foreignKey
     * @param array                                           $foreignKeyIds
     *
     * @return void
     */
    protected function execute($relation, $foreignKey, $foreignKeyIds)
    {
        $relationModel = $relation->getQuery()->getModel();
        $relationModel = new $relationModel();
        $relationModel = $relationModel->withTrashed()->whereIn($foreignKey, $foreignKeyIds);
        $this->run($relationModel->get([$relationModel->getModel()->getKeyName()]));
        if (empty($this->directionData)) {
            $relationModel->{$this->direction}();
        } else {
            $relationModel->{$this->direction}($this->directionData);
        }
    }

    /**
     * Validate the relation method exists and is a type of Eloquent Relation.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     * @param string                             $relation
     *
     * @return void
     */
    protected function validateRelation($model, $relation)
    {
        $class = get_class($model);

        if (!method_exists($model, $relation)) {
            throw new \LogicException(sprintf('%s does not have method \'%s\'.', $class, $relation));
        }

        if (!$model->$relation() instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
            throw new \LogicException(sprintf('%s \'%s\' is not an instance of Illuminate\Database\Eloquent\Relations\Relation.', $class, $relation));
        }
    }

    /**
     * Check if the model is enabled to cascade.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    protected function isCascadable($model)
    {
        return method_exists($model, 'getSoftCascade');
    }
}
