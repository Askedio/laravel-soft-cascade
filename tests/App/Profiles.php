<?php

namespace Askedio\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Profiles extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

    protected $fillable = ['phone'];

    protected $softCascade = ['address'];

    public function address()
    {
        return $this->hasOne('Askedio\Tests\App\Addresses');
    }

    public function user()
    {
        return $this->belongsTo('Askedio\Tests\App\User');
    }

    public function badrelation()
    {
        return $this->hasMany('Askedio\Tests\App\Profile');
    }
}
