<?php

namespace Askedio\Tests;

use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;

class LumenBaseTestCase extends BaseTestCase
{
    /**
     * Boots the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $app->register(\Askedio\SoftCascade\Providers\LumenServiceProvider::class);

        return $app;
    }
}
