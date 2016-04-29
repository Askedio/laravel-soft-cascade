<?php

namespace Askedio\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Profiles extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

    protected $fillable = ['phone'];

    protected $softcascade = ['address'];

    public function address()
    {
        return $this->hasOne('Askedio\Tests\App\Addresses');
    }
}
