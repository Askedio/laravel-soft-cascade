<?php

namespace Askedio\SoftCascade\Listeners;

use Askedio\SoftCascade\SoftCascade;

class CascadeDeleteListener
{
    /**
     * Handel the event for eloquent delete.
     *
     * @param  $event
     * @param  $model
     *
     * @return void
     */
    public function handle($event, $model)
    {
        (new SoftCascade())->cascade($model, 'delete');
    }
}
