<?php

namespace Askedio\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Languages extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

    protected $table = 'languages';

    protected $fillable = ['language'];

    protected $softCascade = ['addresses@restrict'];

    public function addresses()
    {
        return $this->hasMany('Askedio\Tests\App\Addresses');
    }
}
