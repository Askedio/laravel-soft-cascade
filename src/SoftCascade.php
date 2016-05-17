<?php

namespace Askedio\SoftCascade;

/**
 * TO-DO:
 * - Support for ON CASCADE SET NULL
 * - Support for ON CASCADE RESTRICT.
 */

class SoftCascade
{
    protected $direction;

    /**
     * Cascade over Eloquent items.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     * @param string                             $direction delete|restore
     *
     * @return void
     */
    public function cascade($model, $direction)
    {
        $this->direction = $direction;

        $this->run($model);
    }

    /**
     * Run the cascade.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    private function run($model)
    {
        if (!$this->isCascadable($model)) {
            return;
        }

        $this->relations($model, $model->getSoftCascade());
    }

    /**
     * Iterate over the relations.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     * @param array                              $relations
     *
     * @return mixed
     */
    private function relations($model, $relations)
    {
        if (empty($relations)) {
            return;
        }

        foreach ($relations as $relation) {
            $this->validateRelation($model, $relation);
            $this->execute($model->$relation());
        }
    }

    /**
     * Execute delete, or restore.
     *
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     *
     * @return void
     */
    private function execute($relation)
    {
        $this->runNestedRelations($relation);
        $relation->{$this->direction}();
    }

    /**
     * Run nested relations.
     *
     * @param Illuminate\Database\Eloquent\Relations\Relation $relation
     *
     * @return void
     */
    private function runNestedRelations($relation)
    {
        /* TO-DO: pretty sure we can do this on the query w/o get(). */
        /* To-DO: only run withTrashed when restore is triggered. */
        foreach ($relation->withTrashed()->get() as $model) {
            $this->run($model);
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
    private function validateRelation($model, $relation)
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
    private function isCascadable($model)
    {
        return method_exists($model, 'getSoftCascade');
    }
}
