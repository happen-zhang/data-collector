<?php

namespace Zhp\DataCollector\Collectors;

use ArrayAccess;
use Zhp\DataCollector\Interfaces\CollectorInterface;
use Zhp\DataCollector\Exceptions\DataCollectorException;

class AggregatedCollector implements CollectorInterface, ArrayAccess
{
    protected $name;

    protected $mergeProperty;

    protected $sort;

    protected $collectors = [];

    public function __construct($name, $mergeProperty = null, $sort = false)
    {
        $this->name = $name;
        $this->mergeProperty = $mergeProperty;
        $this->sort = $sort;
    }

    public function addCollector(CollectorInterface $collector)
    {
        $this->collectors[$collector->getName()] = $collector;
    }

    public function getCollectors()
    {
        return $this->collectors;
    }

    public function setMergeProperty($property)
    {
        $this->mergeProperty = $property;
    }

    public function getMergeProperty()
    {
        return $this->mergeProperty;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function collect()
    {
        $aggregate = [];
        foreach ($this->collectors as $collector) {
            $data = $collector->collect();
            if ($this->mergeProperty !== null) {
                $data = $data[$this->mergeProperty];
            }

            if (!isset($data['collector'])) {
                $data['collector'] = $collector->getName();
            }

            $aggregate[] = $data;
        }

        return $this->sort($aggregate);
    }

    protected function sort($data)
    {
        if (is_string($this->sort)) {
            $p = $this->sort;
            usort($data, function ($a, $b) use ($p) {
                if ($a[$p] == $b[$p]) {
                    return 0;
                }
                return $a[$p] < $b[$p] ? -1 : 1;
            });
        } elseif ($this->sort === true) {
            sort($data);
        }

        return $data;
    }

    public function getName()
    {
        return $this->name;
    }

    public function offsetSet($key, $value)
    {
        throw new DataCollectorException("AggregatedCollector[] is read-only");
    }

    public function offsetGet($key)
    {
        if (!isset($this->collectors[$key])) {
            throw new DataCollectorException("AggregatedCollector[] is not exists");
        }

        return $this->collectors[$key];
    }

    public function offsetExists($key)
    {
        return isset($this->collectors[$key]);
    }

    public function offsetUnset($key)
    {
        throw new DataCollectorException("AggregatedCollector[] is read-only");
    }
}
