<?php

namespace Askedio\SoftCascade\Listeners;

use Askedio\SoftCascade\EloquentSoftCascade;

class CascadeRestoreListener
{
    /**
     * Handel the event for eloquent restore.
     *
     * @param string                              $event
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     * @throws \Askedio\SoftCascade\Exceptions\SoftCascadeLogicException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter("event"))
     * @noinspection PhpUnusedParameterInspection
     */
    public function handle($event, $model)
    {
        (new EloquentSoftCascade())->cascade($model, 'restore');
    }
}
