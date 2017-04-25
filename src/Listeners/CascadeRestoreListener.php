<?php

namespace Askedio\SoftCascade\Listeners;

use Askedio\SoftCascade\SoftCascade;

class CascadeRestoreListener
{
    /**
     * Handel the event for eloquent restore.
     *
     * @param  $event
     * @param  $model
     *
     * @return void
     */
    public function handle($event, $model)
    {
        (new SoftCascade())->cascade($model, 'restore');
    }
}
