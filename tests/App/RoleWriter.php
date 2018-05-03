<?php

namespace Immofacile\Tests\App;

use Illuminate\Database\Eloquent\Model;

class RoleWriter extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Immofacile\SoftCascade\Traits\SoftCascadeTrait;

    protected $table = 'writers';

    protected $fillable = ['writer_name'];

    protected $softCascade = ['user'];

    /**
     * Get all of the post's comments.
     */
    public function user()
    {
        return $this->morphOne('Immofacile\Tests\App\User', 'role');
    }
}
