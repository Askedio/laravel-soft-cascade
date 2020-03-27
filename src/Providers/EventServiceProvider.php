<?php

namespace Askedio\SoftCascade\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'eloquent.deleting: *'                     => ['Askedio\SoftCascade\Listeners\CascadeDeleteListener'],
        'eloquent.restoring: *'                    => ['Askedio\SoftCascade\Listeners\CascadeRestoreListener'],
        'Illuminate\Database\Events\QueryExecuted' => ['Askedio\SoftCascade\Listeners\CascadeQueryListener'],
    ];
}
