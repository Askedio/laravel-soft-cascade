<?php

namespace Askedio\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = ['languages_id', 'city'];

    public function language()
    {
        return $this->belongsTo('Askedio\Tests\App\Languages');
    }

    public function profile()
    {
        return $this->belongsTo('Askedio\Tests\App\Profile');
    }
}
