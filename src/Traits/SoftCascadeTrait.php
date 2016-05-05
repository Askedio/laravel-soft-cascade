<?php

namespace Askedio\SoftCascade\Traits;

trait SoftCascadeTrait
{
    /**
     * Check if softcasde exists.
     *
     * @return array
     */
    public function getSoftCascade()
    {
        if (!property_exists($this, 'softCascade')) {
            return [];
        }

        return $this->softCascade;
    }
}
