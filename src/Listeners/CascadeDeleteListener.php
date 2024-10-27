<?php

namespace Askedio\SoftCascade\Listeners;

use Askedio\SoftCascade\EloquentSoftCascade;

class CascadeDeleteListener
{
    /**
     * Handel the event for eloquent delete.
     *
     * @param string                              $event
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter("event"))
     * @noinspection PhpUnusedParameterInspection
     */
    public function handle($event, $model)
    {
        (new EloquentSoftCascade())->cascade($model, 'delete');
    }
}
