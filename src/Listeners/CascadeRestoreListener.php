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
     * @throws \Askedio\SoftCascade\Exceptions\SoftCascadeLogicException
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter("event"))
     * @noinspection PhpUnusedParameterInspection
     */
    public function handle($event, $model)
    {
        (new EloquentSoftCascade())->cascade($model, 'restore');
    }
}
