<?php

namespace Zhp\DataCollector\Collectors;

class ConfigCollector extends AbstractCollector
{
    protected $data;

    protected $collectorName;

    public function __construct(array $data = [], $name = 'config')
    {
        $this->collectorName = $name;
        $this->data = $data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function collect()
    {
        $data = [];

        foreach ($this->data as $k => $v) {
            if (!is_string($v)) {
                $v = $this->getDataFormatter()->formatVar($v);
            }

            $data[$k] = $v;
        }

        return $data;
    }
}
