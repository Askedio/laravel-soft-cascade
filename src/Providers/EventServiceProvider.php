<?php

namespace Immofacile\SoftCascade\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
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
