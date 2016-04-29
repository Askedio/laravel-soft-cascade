<?php

namespace Askedio\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

    protected $fillable = ['city'];
}
