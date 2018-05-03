<?php

namespace Immofacile\Tests\App;

class BadRelationB extends User
{
    protected $softCascade = ['badrelation'];
}
