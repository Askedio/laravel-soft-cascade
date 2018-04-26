<?php

namespace Askedio\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;

    protected $table = 'customers';

    protected $fillable = ['name'];

    protected $softCascade = ['phone'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function phone()
    {
        return $this->hasOne(Phone::class);
    }
}
