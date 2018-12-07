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
        if (getenv('APP_ENV') === 'testing') {
            return []; // Disable cascade soft delete for unit test.
        }

        if (!property_exists($this, 'softCascade')) {
            return [];
        }

        return $this->softCascade;
    }
}
