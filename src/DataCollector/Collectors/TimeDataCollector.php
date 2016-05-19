<?php

namespace Zhp\DataCollector\Collectors;

use Closure;
use Zhp\DataCollector\Exceptions\DataCollectorException;

class TimeDataCollector extends AbstractCollector
{
    protected $requestStartTime;

    protected $requestEndTime;

    protected $startedMeasures = [];

    protected $measures = [];

    protected $collectorName = 'time';

    public function __construct($requestStartTime = null)
    {
        if ($requestStartTime === null) {
            if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
                $requestStartTime = $_SERVER['REQUEST_TIME_FLOAT'];
            } else {
                $requestStartTime = microtime(true);
            }
        }

        $this->requestStartTime = $requestStartTime;
    }

    public function startMeasure($name, $label = null, $collector = null)
    {
        $start = microtime(true);
        $this->startedMeasures[$name] = [
            'label' => $label ?: $name,
            'start' => $start,
            'collector' => $collector
        ];
    }

    public function hasStartedMeasure($name)
    {
        return isset($this->startedMeasures[$name]);
    }

    public function stopMeasure($name, $params = [])
    {
        $end = microtime(true);
        if (!$this->hasStartedMeasure($name)) {
            throw new DataCollectorException("Failed stopping measure '$name' because it hasn't been started");
        }

        $this->addMeasure(
            $this->startedMeasures[$name]['label'],
            $this->startedMeasures[$name]['start'],
            $end,
            $params,
            $this->startedMeasures[$name]['collector']
        );

        unset($this->startedMeasures[$name]);
    }

    public function addMeasure($label, $start, $end, $params = [], $collector = null)
    {
        $this->measures[] = [
            'label' => $label,
            'start' => $start,
            'relative_start' => $start - $this->requestStartTime,
            'end' => $end,
            'relative_end' => $end - $this->requestEndTime,
            'duration' => $end - $start,
            'duration_str' => $this->getDataFormatter()->formatDuration($end - $start),
            'params' => $params,
            'collector' => $collector
        ];
    }

    public function measure($label, Closure $closure, $collector = null)
    {
        $name = spl_object_hash($closure);
        $this->startMeasure($name, $label, $collector);
        $result = $closure();
        $params = is_array($result) ? $result : [];
        $this->stopMeasure($name, $params);
    }

    public function getMeasures()
    {
        return $this->measures;
    }

    public function getRequestStartTime()
    {
        return $this->requestStartTime;
    }

    public function getRequestEndTime()
    {
        return $this->requestEndTime;
    }

    public function getRequestDuration()
    {
        if ($this->requestEndTime !== null) {
            return $this->requestEndTime - $this->requestStartTime;
        }

        return microtime(true) - $this->requestStartTime;
    }

    public function collect()
    {
        $this->requestEndTime = microtime(true);
        foreach (array_keys($this->startedMeasures) as $name) {
            $this->stopMeasure($name);
        }

        return [
            'start' => $this->requestStartTime,
            'end' => $this->requestEndTime,
            'duration' => $this->getRequestDuration(),
            'duration_str' => $this->getDataFormatter()->formatDuration($this->getRequestDuration()),
            'measures' => array_values($this->measures)
        ];
    }
}
