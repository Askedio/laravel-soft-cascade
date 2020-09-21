![Header](https://i.imgur.com/fKhbljT.png)

[![Build Status](https://travis-ci.org/Askedio/laravel-soft-cascade.svg?branch=master)](https://travis-ci.org/Askedio/laravel-soft-cascade)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/58877b88ab38457695217851658a443b)](https://www.codacy.com/app/gcphost/laravel-soft-cascade?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Askedio/laravel-soft-cascade&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/58877b88ab38457695217851658a443b)](https://www.codacy.com/app/gcphost/laravel-soft-cascade?utm_source=github.com&utm_medium=referral&utm_content=Askedio/laravel-soft-cascade&utm_campaign=Badge_Coverage)
[![StyleCI Badge](https://styleci.io/repos/57394710/shield)](https://styleci.io/repos/57394710)

# Laravel/Lumen Soft Cascade Delete & Restore
Cascade delete and restore when using the Laravel or Lumen SoftDeletes feature.

# Why do I need it?
### To make soft deleting and restoring relations easy.
If you enjoy features like MySQL cascade deleting but want to use Laravels SoftDeletes feature you'll need to do some extra steps to ensure your relations are properly deleted or restored.

This package is intended to replace those steps with a simple array that defines the relations you want to cascade.

# Installation
Install with composer
~~~
composer require askedio/laravel-soft-cascade
~~~

From Laravel 5.5 onwards, it's possible to take advantage of auto-discovery of the service provider.
For Laravel versions before 5.5, you must register the service provider in your config/app.php

~~~
Askedio\SoftCascade\Providers\GenericServiceProvider::class,
~~~

Lumen does not support the auto-discovery feature, you should manually add the provider.

~~~
Askedio\SoftCascade\Providers\LumenServiceProvider::class,
~~~



# Usage
In your `Model` enable the trait and define `$softCascade`. [Example](https://github.com/Askedio/laravel5-soft-cascade/blob/master/tests/App/User.php).
~~~
use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

protected $softCascade = ['profiles'];
~~~
For restricted relation use. [Example](https://github.com/Askedio/laravel5-soft-cascade/blob/master/tests/App/Languages.php).
~~~
use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

protected $softCascade = ['addresses@restrict'];
~~~
`$softCascade` is an array of your relation names, in the [example](https://github.com/Askedio/laravel5-soft-cascade/blob/master/tests/App/User.php) you'll see we've defined `function profiles()` for the relation.

Nested relations work by defining `$softCascade` in the related `Model` as you can see [here](https://github.com/Askedio/laravel5-soft-cascade/blob/master/tests/App/Profiles.php).

After you've defined your relations you can simply trigger `delete()` or `restore()` on your `Model` and your relations will have the same task performed.

~~~
User::first()->delete();
User::withTrashed()->first()->restore();
~~~

It can also be used with query builder in this way because query builder listener is executed after query, we need to use transaction for rollback query on error due to restricted relationships

~~~
try {
    DB::beginTransaction(); //Start db transaction for rollback query when error
    User::limit(2)->delete();
	User::withTrashed()->limit(2)->restore();
    DB::commit(); //Commit the query
} catch (\Exception $e) {
    DB::rollBack(); //Rollback the query
    //Optional, if we need to continue execution only rollback transaction and save message on variable
    throw new \Askedio\SoftCascade\Exceptions\SoftCascadeLogicException($e->getMessage()); 
}
~~~

# Supported Databases
* MySQL
* PostgreSQL
* SQLite
* SQL Server

# Testing
I have written some very basic tests, certainly more needs to be done here. If you find this useful please help by testing other databases or writing better unit tests because I must move on.

# Issues & Contributing
I will be using this with MySQL in a new API so any issues I find related to my use will be resolved. If you find an issue with MySQL please report it and I will fix it.

If you are using another database and have issues please contribute by submitting a pull request. I do not have time to test this with other database but assume all would work.

