<?php

namespace Immofacile\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Profiles extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Immofacile\SoftCascade\Traits\SoftCascadeTrait;

    protected $fillable = ['phone'];

    protected $softCascade = ['address'];

    public function address()
    {
        return $this->hasOne('Immofacile\Tests\App\Addresses');
    }

    public function user()
    {
        return $this->belongsTo('Immofacile\Tests\App\Use');
    }

    public function badrelation()
    {
        return $this->hasMany('Immofacile\Tests\App\Profile');
    }
}
