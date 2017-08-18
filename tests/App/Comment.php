<?php

namespace Askedio\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'comments';

    protected $fillable = ['body'];

    /**
     * Get all of the owning commentable models.
     */
    public function commentable()
    {
        return $this->morphTo();
    }
}
