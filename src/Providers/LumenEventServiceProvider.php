<?php

namespace Askedio\SoftCascade\Providers;

use Askedio\SoftCascade\Listeners\CascadeDeleteListener;
use Askedio\SoftCascade\Listeners\CascadeQueryListener;
use Askedio\SoftCascade\Listeners\CascadeRestoreListener;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class LumenEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @return void
     */
    public function register()
    {
        Event::listen('eloquent.deleting: *', [CascadeDeleteListener::class, 'handle']);
        Event::listen('eloquent.restoring: *', [CascadeRestoreListener::class, 'handle']);
        Event::listen(QueryExecuted::class, [CascadeQueryListener::class, 'handle']);
    }
}
