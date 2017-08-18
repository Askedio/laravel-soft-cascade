<?php

namespace Askedio\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'videos';

    protected $fillable = ['title', 'url'];

    protected $softCascade = ['comments'];

    /**
     * Get all of the video's comments.
     */
    public function comments()
    {
        return $this->morphMany('Askedio\Tests\App\Comment', 'commentable');
    }
}
