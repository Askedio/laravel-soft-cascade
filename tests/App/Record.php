<?php

namespace Askedio\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    public $primaryKey = null;

    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'records';

    protected $fillable = ['record_id', 'time', 'value'];
}
