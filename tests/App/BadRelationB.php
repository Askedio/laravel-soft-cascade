<?php

namespace Askedio\Tests\App;

class BadRelationB extends User
{
    protected $softCascade = ['badrelation'];
}
