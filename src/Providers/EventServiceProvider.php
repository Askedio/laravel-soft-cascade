<?php

namespace Askedio\SoftCascade\Providers;

use Askedio\SoftCascade\Listeners\CascadeDeleteListener;
use Askedio\SoftCascade\Listeners\CascadeQueryListener;
use Askedio\SoftCascade\Listeners\CascadeRestoreListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'eloquent.deleting: *'          => [CascadeDeleteListener::class],
        'eloquent.restoring: *'         => [CascadeRestoreListener::class],
        CascadeQueryListener::EVENT     => [CascadeQueryListener::class],
    ];
}
