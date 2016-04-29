# Laravel 5 Soft Cascade
Cascade delete and restore when using SoftDeletes.

# Installation
~~~
  composer require askedio/laravel5-soft-cascade
~~~

Register service provider in config/app.php

Enable trait and softcascade in your Models
~~~
use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

protected $softcascade = ['profiles'];
~~~