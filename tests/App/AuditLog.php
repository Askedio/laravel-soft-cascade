<?php

namespace Askedio\Tests\App;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    // This model does not use SoftDeletes, and should be HardDeleted if the parent is SoftDeleted

    protected $table = 'audit_logs';

    protected $fillable = ['content'];

    public function logable()
    {
        return $this->morphTo();
    }
}
