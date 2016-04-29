<?php

namespace Askedio\SoftCascade\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CascadeDeleteListener extends Cascade
{
    /**
     * Handel the event for eloquent delete.
     * @param  $model
     * @return void
     */
    public function handle($model)
    {
        $this->cascade($model, 'delete');
    }
}
