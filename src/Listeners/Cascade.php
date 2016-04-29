<?php

namespace Askedio\SoftCascade\Listeners;

class Cascade
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
        if (!$this->cascadable($model)) {
            return;
        }

        $this->relations($model, $model->getSoftCascade());
    }

    /**
     * Run the relations.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     * @param array                              $relations
     *
     * @return void
     */
    private function relations($model, $relations)
    {
        if (empty($relations)) {
            return;
        }

        foreach ($relations as $relation) {
            $this->items($model->$relation());
        }
    }

    /**
     * Run the items.
     *
     * @param array $relation
     *
     * @return void
     */
    private function items($relation)
    {
        foreach ($relation->withTrashed()->get() as $item) {
            $this->run($item);
        }
        $relation->{$this->direction}();
    }

    /**
     * Check if the model is enabled to cascade.
     *
     * @param Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    private function cascadable($model)
    {
        return method_exists($model, 'getSoftCascade');
    }
}
