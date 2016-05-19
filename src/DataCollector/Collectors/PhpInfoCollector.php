<?php

namespace Zhp\DataCollector\Collectors;

class PhpInfoCollector extends AbstractCollector
{
    protected $collectorName = 'php';

    public function collect()
    {
        return [
            'version' => PHP_VERSION,
            'interface' => PHP_SAPI
        ];
    }
}
