<?php

namespace Askedio\SoftCascade\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class LumenEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    public function register()
    {
        Event::listen(
            'eloquent.deleting: *',
            ['Askedio\SoftCascade\Listeners\CascadeDeleteListener', 'handle']
        );
        Event::listen(
            'eloquent.restoring: *',
            ['Askedio\SoftCascade\Listeners\CascadeRestoreListener', 'handle']
        );
        Event::listen(
            'Illuminate\Database\Events\QueryExecuted',
            ['Askedio\SoftCascade\Listeners\CascadeQueryListener', 'handle']
        );
    }
}
