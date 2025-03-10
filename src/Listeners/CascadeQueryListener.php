<?php

namespace Askedio\SoftCascade\Listeners;

use Askedio\SoftCascade\QueryBuilderSoftCascade;
use Askedio\SoftCascade\Traits\ChecksCascading;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Str;

class CascadeQueryListener
{
    use ChecksCascading;

    public const EVENT = QueryExecuted::class;

    /**
     * Check in the backtrace, if models where updated by Builder::delete() or Builder::update().
     *
     * @return array{
     *             builder:       object,
     *             direction:     string,
     *             directionData: array,
     *         } | null
     */
    private function checkForCascadeEvent(): ?array
    {
        /**
         * @var \Illuminate\Support\Collection<array-key, array{
         *          function: string,
         *          line:     int,
         *          file:     string,
         *          class?:   string,
         *          object?:  object,
         *          type:     string,
         *          args?:    array,
         *      }> $traces
         */
        $traces = collect(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 30));

        // we limit the backtrace to the current and the previous query (=2 "QueryExecuted"-events),
        // as otherwise the cascade might be triggered multiple times per user call.
        $queryExecutedEventLimit = 2;
        $traces = $traces->takeUntil(function (array $backtrace) use (&$queryExecutedEventLimit) {
            $btClass = $backtrace['class'] ?? null;
            $btFunction = $backtrace['function'] ?? null;
            $btEvent = $backtrace['args'][0] ?? null;
            if ($btClass === Connection::class && $btFunction === 'event' && $btEvent === static::EVENT) {
                $queryExecutedEventLimit--;
            }

            return $queryExecutedEventLimit <= 0;
        });

        foreach ($traces as $backtrace) {
            $btClass = $backtrace['class'] ?? null;
            if (!is_a($btClass, Builder::class, true)) {
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

        if ($event !== null) {
            $builder = $event['builder'];

            // add `withTrashed()`, if the model has SoftDeletes
            // otherwise, we can just skip it
            if (method_exists($builder, 'withTrashed') || $builder->hasMacro('withTrashed')) {
                $builder->withTrashed();
            }

            $keyName = $builder->getModel()->getKeyName();

            if ($keyName === null || !$this->hasCascadingRelations($builder->getModel())) {
                // If model doesn't have any primary key, there will be no relations
                return;
            }

            $model = $builder->get([$keyName]);
            (new QueryBuilderSoftCascade())->cascade($model, $event['direction'], $event['directionData']);
        }
    }
}
