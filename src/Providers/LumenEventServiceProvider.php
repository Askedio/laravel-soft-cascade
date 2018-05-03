<?php

namespace Immofacile\SoftCascade\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class LumenEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
      'eloquent.deleting: *'                     => ['Immofacile\SoftCascade\Listeners\CascadeDeleteListener'],
      'eloquent.restoring: *'                    => ['Immofacile\SoftCascade\Listeners\CascadeRestoreListener'],
      'Illuminate\Database\Events\QueryExecuted' => ['Immofacile\SoftCascade\Listeners\CascadeQueryListener'],
    ];
}
