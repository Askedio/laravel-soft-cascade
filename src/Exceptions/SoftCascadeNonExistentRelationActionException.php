<?php

namespace Askedio\SoftCascade\Exceptions;

use RuntimeException;

class SoftCascadeNonExistentRelationActionException extends RuntimeException
{
    /**
     * Name of the affected relation.
     *
     * @var string
     */
    protected $relation;

    /**
     * Set the affected relation.
     *
     * @param string $relation
     *
     * @return $this
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;

        $this->message = "Non existing relation action [{$relation}]";

        return $this;
    }

    /**
     * Get the affected relation.
     *
     * @return string
     */
    public function getRelation()
    {
        return $this->relation;
    }
}
