<?php

namespace Zhp\DataCollector;

use Zhp\DataCollector\Interfaces\CollectorInterface;
use Zhp\DataCollector\Interfaces\RequestIdGeneratorInterface;
use Zhp\DataCollector\Interfaces\StorageInterface;
use Zhp\DataCollector\Generators\RequestIdGenerator;
use Zhp\DataCollector\Exceptions\DataCollectorException;

class DataCollector implements \ArrayAccess
{
    protected $collectors = [];

    protected $data;

    protected $requestId;

    protected $storage;

    protected $requestIdGenerator;

    public function addCollector(CollectorInterface $collector)
    {
        if ($collector->getName() === '__meta') {
            throw new DataCollectorException("'__meta' is a reserved name and cannot be used as a collector name");
        }

        if (isset($this->collectors[$collector->getName()])) {
            throw new DataCollectorException("'{$collector->getName()}' is already a registered collector");
        }

        $this->collectors[$collector->getName()] = $collector;
        return $this;
    }

    public function hasCollector($name)
    {
        return isset($this->collectors[$name]);
    }

    public function getCollector($name)
    {
        if (!isset($this->collectors[$name])) {
            throw new DataCollectorException("'$name' is not a registered collector");
        }

        return $this->collectors[$name];
    }

    public function getCollectors()
    {
        return $this->collectors;
    }

    public function setRequestIdGenerator(RequestIdGeneratorInterface $generator)
    {
        $this->requestIdGenerator = $generator;
        return $this;
    }

    public function getRequestIdGenerator()
    {
        if ($this->requestIdGenerator === null) {
            $this->requestIdGenerator = new RequestIdGenerator();
        }

        return $this->requestIdGenerator;
    }

    public function getCurrentRequestId()
    {
        if ($this->requestId === null) {
            $this->requestId = $this->getRequestIdGenerator()->generate();
        }

        return $this->requestId;
    }

    public function setStorage(StorageInterface $storage = null)
    {
        $this->storage = $storage;
        return $this;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function isDataPersisted()
    {
        return $this->storage !== null;
    }

    public function getData($force = false)
    {
        if ($force === true || $this->data === null) {
            $this->collect();
        }

        return $this->data;
    }

    public function collect()
    {
        $this->data = [
            '__meta' => [
                'id' => $this->getCurrentRequestId(),
                'datetime' => date('Y-m-d H:i:s'),
                'utime' => microtime(true),
                'method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null,
                'uri' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null,
                'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null
            ]
        ];

        foreach ($this->collectors as $name => $collector) {
            $this->data[$name] = $collector->collect();
        }

        array_walk_recursive($this->data, function (&$item) {
            if (is_string($item) && !mb_check_encoding($item, 'UTF-8')) {
                $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
        });

        if ($this->isDataPersisted()) {
            $this->storage->save($this->getCurrentRequestId(), $this->data);
        }

        return $this->data;
    }

    public function offsetSet($key, $value)
    {
        throw new DataCollectorException('DataCollector[] is read-only');
    }

    public function offsetGet($key)
    {
        return $this->getCollector($key);
    }

    public function offsetExists($key)
    {
        return $this->hasCollector($key);
    }

    public function offsetUnset($key)
    {
        throw new DataCollectorException("DataCollector[] is read-only");
    }
}
