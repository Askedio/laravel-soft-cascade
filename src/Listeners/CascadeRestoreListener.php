<?php

namespace Askedio\SoftCascade\Listeners;

class CascadeRestoreListener extends Cascade
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
        $this->cascade($model, 'restore');
    }
}
