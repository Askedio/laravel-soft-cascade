<?php

namespace Askedio\SoftCascade\Listeners;

use Askedio\SoftCascade\QueryBuilderSoftCascade;

class CascadeQueryListener
{

    protected $listenClass = 'Illuminate\Database\Eloquent\Builder';

    /**
     * Return the backtrace will be use to get model object and function
     * 
     * @return type
     */
    private function getBacktraceUse()
    {
        $debugBacktrace = collect(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 30))->filter(function($backtrace)  {
            return @$backtrace['class'] === $this->listenClass;
        })->first();
        $prueba = collect(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 30))->filter(function($backtrace)  {
            return str_contains(@$debugBacktrace['file'], 'SoftDeletingScope.php');
        })->first();
        if (!is_null($prueba)) {
            dd($prueba);
        }
        $checkBacktrace = null;
        if (!is_null($debugBacktrace) && str_contains(@$debugBacktrace['file'], 'Illuminate/Database/Eloquent/SoftDeletingScope.php') && @$debugBacktrace['function'] == 'update') {
            $checkBacktrace = [
                'object' => $debugBacktrace['object'],
                'function' => $debugBacktrace['function'],
                'args' => $debugBacktrace['args'][0]
            ];
        }
        return $checkBacktrace;
    }

    /**
     * Handel the event for eloquent delete.
     *
     * @return void
     */
    public function handle($query)
    {
        $checkBacktrace = $this->getBacktraceUse();
        if (!is_null($checkBacktrace)) {
            $model = $checkBacktrace['object']->withTrashed()->get([$checkBacktrace['object']->getModel()->getKeyName()]);
            (new QueryBuilderSoftCascade())->cascade($model, $checkBacktrace['function'], $checkBacktrace['args']);
        }
    }
}
