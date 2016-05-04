<?php

namespace Askedio\SoftCascade\Listeners;

use Askedio\SoftCascade\SoftCascade;

class CascadeRestoreListener
{
    /**
     * Handel the event for eloquent restore.
     *
     * @param  $model
     *
     * @return void
     */
    public function handle($model)
    {
        (new SoftCascade())->cascade($model, 'restore');
    }
}
