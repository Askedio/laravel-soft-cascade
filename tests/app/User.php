<?php

namespace Askedio\SoftCascade\Tests\App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

    protected $fillable = ['name', 'email', 'password'];

    protected $softcascade = ['profiles'];

    public function profiles()
    {
        return $this->hasMany('Askedio\SoftCascade\Tests\App\Profiles');
    }
}
