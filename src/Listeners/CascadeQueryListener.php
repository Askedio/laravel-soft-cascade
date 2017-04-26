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
     * Return the backtrace will be use to get model object and function
     * 
     * @return type
     */
    private function getBacktraceUse()
    {
        $listenFunctionsKeys = array_keys($this->listenFuncions);
        $debugBacktrace = collect(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 14));
        $checkBacktrace = null;
        $debugBacktrace->each(function($backtrace) use ($listenFunctionsKeys, &$checkBacktrace) { 
            if (@$$backtrace['class'] == $this->listenClass && in_array(@$$backtrace['function'], $listenFunctionsKeys)) { //For direct method
                $checkBacktrace = [
                    'object' => $backtrace['object'],
                    'function' => $backtrace['function']
                ];
            } else if (@$backtrace['class'] == $this->listenClass && @$backtrace['function'] == '__call') { //For __call
                if (in_array($backtrace['args'][0], $listenFunctionsKeys)) {
                    $checkBacktrace = [
                        'object' => $backtrace['object'],
                        'function' => $backtrace['args'][0]
                    ];
                }
            }
        });
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
        $model = null;
        if (!is_null($checkBacktrace)) {
            $model = $checkBacktrace['object'];
            $modelFilters = $this->listenFuncions[$checkBacktrace['function']];
            foreach ($modelFilters as $method => $calls) {
                foreach ($calls as $arguments) {
                    $model = call_user_func_array(array($model, $method), $arguments);
                }
            }
            $model = $model->get();
            (new SoftCascade())->cascade($model, $checkBacktrace['function']);
        }
    }
}
