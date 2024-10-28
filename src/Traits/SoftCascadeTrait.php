<?php

namespace Askedio\SoftCascade\Traits;

trait SoftCascadeTrait
{
    /**
     * Check if softCascade exists.
     *
     * @return string[]
     */
    public function getSoftCascade()
    {
        if (!property_exists($this, 'softCascade')) {
            return [];
        }

        return $this->softCascade;
    }
}
