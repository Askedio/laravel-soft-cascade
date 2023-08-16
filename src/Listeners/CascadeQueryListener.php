<?php

namespace Askedio\SoftCascade\Listeners;

use Askedio\SoftCascade\QueryBuilderSoftCascade;
use BadMethodCallException;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Str;

class CascadeQueryListener
{
    const EVENT = QueryExecuted::class;

    /**
     * Check in the backtrace, if models where updated by Builder::delete() or Builder::update().
     */
    private function checkForCascadeEvent(): ?array
    {
        $traces = collect(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 30));

        // we limit the backtrace to the current and the previous query (=2 "QueryExecuted"-events),
        // as otherwise the cascade might be triggered multiple times per user call.
        $queryExecutedEventLimit = 2;
        $traces = $traces->takeUntil(function (array $backtrace) use (&$queryExecutedEventLimit) {
            $btClass = $backtrace['class'] ?? null;
            $btFunction = $backtrace['function'] ?? null;
            $btEvent = $backtrace['args'][0] ?? null;
            if ($btClass === Connection::class && $btFunction === 'event' && $btEvent === static::EVENT) {
                $queryExecutedEventLimit = $queryExecutedEventLimit - 1;
            }

            return $queryExecutedEventLimit <= 0;
        });

        foreach ($traces as $backtrace) {
            $btClass = $backtrace['class'] ?? null;
            if (!is_a($btClass, \Illuminate\Database\Eloquent\Builder::class, true)) {
                continue;
            }

            $btFunction = $backtrace['function'] ?? null;
            $btFile = $backtrace['file'] ?? null;

            // check for all deletes
            if ($btFunction === 'delete') {
                return [
                    'builder'       => $backtrace['object'],
                    'direction'     => $btFunction,
                    'directionData' => [],
                ];
            }

            // check for updates, which where triggered from SoftDelete (set or unset deleted_at-column)
            if ($btFunction === 'update' && Str::contains($btFile, SoftDeletingScope::class)) {
                return [
                    'builder'       => $backtrace['object'],
                    'direction'     => $btFunction,
                    'directionData' => $backtrace['args'][0],
                ];
            }
        }

        return null;
    }

    /**
     * Handel the event for eloquent delete.
     */
    public function handle(): void
    {
        $event = $this->checkForCascadeEvent();

        if (!is_null($event)) {
            $builder = $event['builder'];

            try {
                $builder->withTrashed();
            } catch (BadMethodCallException $e) {
                // add `withTrashed()`, if the model has SoftDeletes
                // otherwise, we can just skip it
            }

            $model = $builder->get([$builder->getModel()->getKeyName()]);
            (new QueryBuilderSoftCascade())->cascade($model, $event['direction'], $event['directionData']);
        }
    }
}
