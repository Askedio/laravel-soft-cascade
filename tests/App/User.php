<?php

namespace Immofacile\Tests\App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Immofacile\SoftCascade\Traits\SoftCascadeTrait;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password'];

    protected $softCascade = ['profiles'];

    public function profiles()
    {
        return $this->hasMany('Immofacile\Tests\App\Profiles');
    }

    public function role()
    {
        return $this->morphTo();
    }
}
