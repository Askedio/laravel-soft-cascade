![Header](https://i.imgur.com/fKhbljT.png)

[![Build Status](https://travis-ci.org/Askedio/laravel5-soft-cascade.svg?branch=master)](https://travis-ci.org/Askedio/laravel5-soft-cascade)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/019b9dbd700f42b6a165742c72e64445)](https://www.codacy.com/app/gcphost/laravel5-soft-cascade?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Askedio/laravel5-soft-cascade&amp;utm_campaign=Badge_Grade) [![StyleCI Badge](https://styleci.io/repos/57394710/shield)](https://styleci.io/repos/57394710)

# Laravel 5 Soft Cascade
Cascade delete and restore when using the Laravel SoftDeletes feature.

# Why do I need it?
### To make it easy to soft delete and restore relations.
If you enjoy features like MySQL cascade deleting but want to use Laravels SoftDeletes feature you'll need to do some extra steps to ensure your relations are properly deleted or restored.

This package is intended to replace those steps with a simple array that defines the relations you want to cascade.

# Installation
Install with composer
~~~
composer require askedio/laravel5-soft-cascade
~~~

Register the service provider in your config/app.php
~~~
Askedio\SoftCascade\Providers\GenericServiceProvider::class,
~~~

In your Model(s), enable the trait and define $softcascade. [Example](https://github.com/Askedio/laravel5-soft-cascade/blob/master/tests/app/User.php).
~~~
use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

protected $softcascade = ['profiles'];
~~~
`$softcascade` is an array of your relation names, in the [example](https://github.com/Askedio/laravel5-soft-cascade/blob/master/tests/app/User.php) you'll see we've defined `function profiles()` for the relation.

Nested relations work by defining `$softcascade` in the related Model as you can see [here](https://github.com/Askedio/laravel5-soft-cascade/blob/master/tests/app/Profiles.php).



# Supported Databases
* MySQL
* SQLite

# Testing
I have written some very basic tests, certainly more needs to be done here. If you find this useful please help by testing other databases or writing better unit tests because I must move on.

# Issues & Contributing
I will be using this with MySQL in a new API so any issues I find related to my use will be resolved. If you find an issue with MySQL please report it and I will fix it.

If you are using another database and have issues please contribute by submitting a pull request. I do not have time to test this with other database but assume all would work.

