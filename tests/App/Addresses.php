<?php

namespace Immofacile\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = ['languages_id', 'city'];

    public function language()
    {
        return $this->belongsTo('Immofacile\Tests\App\Languages');
    }

    public function profile()
    {
        return $this->belongsTo('Immofacile\Tests\App\Profile');
    }
}
