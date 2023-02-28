<?php

namespace Askedio\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

    protected $fillable = ['languages_id', 'city'];

    protected $softCascade = ['logs'];

    public function language()
    {
        return $this->belongsTo('Askedio\Tests\App\Languages');
    }

    public function profile()
    {
        return $this->belongsTo('Askedio\Tests\App\Profile');
    }

    public function logs()
    {
        return $this->morphMany('Askedio\Tests\App\AuditLog', 'loggable');
    }
}
