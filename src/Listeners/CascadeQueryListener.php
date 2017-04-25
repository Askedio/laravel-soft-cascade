<?php

namespace Askedio\SoftCascade\Listeners;

use Askedio\SoftCascade\SoftCascade;

class CascadeQueryListener
{
    protected $listenFuncions = [
        'delete'  => [
            'withTrashed' => [
                []
            ]
        ],
        'restore' => [
            'withTrashed' => [
                []
            ]
        ]
    ];

    protected $listenClass = 'Illuminate\Database\Eloquent\Builder';

    /**
     * Handel the event for eloquent delete.
     *
     * @param  $queryExecuted
     *
     * @return void
     */
    public function handle($queryExecuted)
    {
        $debugBacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 14);
        $listenFunctionsKeys = array_keys($this->listenFuncions);
        $checkBacktrace = null;
        $model = null;
        if (
            isset($debugBacktrace[13]) && isset($debugBacktrace[13]['class']) && 
            isset($debugBacktrace[13]['function']) && $debugBacktrace[13]['class'] == $this->listenClass && 
            in_array($debugBacktrace[13]['function'], $listenFunctionsKeys)
        ) { //For direct method
            $checkBacktrace = [
                'object' => $debugBacktrace[13]['object'],
                'function' => $debugBacktrace[13]['function']
            ];
        } else if (
            isset($debugBacktrace[12]) && isset($debugBacktrace[12]['class']) && 
            isset($debugBacktrace[12]['function']) && $debugBacktrace[12]['class'] == $this->listenClass && 
            $debugBacktrace[12]['function'] == '__call'
        ) { //For __call
            $checkBacktrace = [
                'object' => $debugBacktrace[12]['object'],
                'function' => $debugBacktrace[12]['args'][0]
            ];
        }
        if (!is_null($checkBacktrace)) {
            $model = $checkBacktrace['object'];
            $modelFilters = $this->listenFuncions[$checkBacktrace['function']];
            foreach ($modelFilters as $method => $calls) {
                foreach ($calls as $arguments) {
                    $model = call_user_func_array(array($model, $method), $arguments);
                }
            }
            // dd($checkBacktrace['function']);
            $model = $model->get();
            (new SoftCascade())->cascade($model, $checkBacktrace['function']);
        }
    }
}
