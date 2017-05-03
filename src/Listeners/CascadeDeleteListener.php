<?php

namespace Askedio\SoftCascade\Listeners;

use Askedio\SoftCascade\EloquentSoftCascade;

class CascadeDeleteListener
{
    /**
     * Handel the event for eloquent delete.
     *
     * @param  $event
     * @param  $model
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter("event"))
     */
    public function handle($event, $model)
    {
        (new EloquentSoftCascade())->cascade($model, 'delete');
    }
}
