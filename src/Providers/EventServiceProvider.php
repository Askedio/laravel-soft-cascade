<?php

namespace Askedio\SoftCascade\Providers;

use Illuminate\Support\ServiceProvider;
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
      'eloquent.deleting: *'  => ['Askedio\SoftCascade\Listeners\CascadeDeleteListener'],
      'eloquent.restoring: *' => ['Askedio\SoftCascade\Listeners\CascadeRestoreListener'],
      'Illuminate\Database\Events\QueryExecuted' => ['Askedio\SoftCascade\Listeners\CascadeQueryListener']
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
