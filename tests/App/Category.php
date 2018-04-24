<?php

namespace Askedio\Tests\App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Askedio\SoftCascade\Traits\SoftCascadeTrait;
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'categories';

    protected $fillable = ['name'];

    protected $softCascade = ['posts'];

    /**
     * Get all posts.
     */
    public function posts()
    {
        return $this->belongsToMany('Askedio\Tests\App\Post');
    }
}
