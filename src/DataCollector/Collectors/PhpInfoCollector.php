<?php

namespace Zhp\DataCollector\Collectors;

class PhpInfoCollector extends AbstractCollector
{
    public function collect()
    {
        return [
            'version' => PHP_VERSION,
            'interface' => PHP_SAPI
        ];
    }

    public function getName()
    {
        return 'php';
    }
}
