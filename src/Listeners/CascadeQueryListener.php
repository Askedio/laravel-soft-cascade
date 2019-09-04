<?php

namespace Askedio\SoftCascade\Listeners;

use Askedio\SoftCascade\QueryBuilderSoftCascade;
use Illuminate\Support\Str;

class CascadeQueryListener
{
    protected $listenClass = 'Illuminate\Database\Eloquent\Builder';

    /**
     * Return the backtrace will be use to get model object and function.
     *
     * @return type
     */
    private function getBacktraceUse()
    {
        $debugBacktrace = collect(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 30))->filter(function ($backtrace) {
            $backtraceClass = (isset($backtrace['class'])) ? $backtrace['class'] : null;

            return $backtraceClass === $this->listenClass;
        })->first();
        $checkBacktrace = null;
        $backtraceFile = (isset($debugBacktrace['file'])) ? $debugBacktrace['file'] : null;
        $backtraceFunction = (isset($debugBacktrace['function'])) ? $debugBacktrace['function'] : null;
        if (!is_null($debugBacktrace) && Str::contains($backtraceFile, 'Illuminate/Database/Eloquent/SoftDeletingScope.php') && $backtraceFunction == 'update') {
            $checkBacktrace = [
                'object'   => $debugBacktrace['object'],
                'function' => $debugBacktrace['function'],
                'args'     => $debugBacktrace['args'][0],
            ];
        }

        return $checkBacktrace;
    }

    /**
     * Handel the event for eloquent delete.
     *
     * @return void
     */
    public function handle()
    {
        $checkBacktrace = $this->getBacktraceUse();
        if (!is_null($checkBacktrace)) {
            $model = $checkBacktrace['object']->withTrashed()->get([$checkBacktrace['object']->getModel()->getKeyName()]);
            (new QueryBuilderSoftCascade())->cascade($model, $checkBacktrace['function'], $checkBacktrace['args']);
        }
    }
}
