<?php

namespace Zhp\DataCollector\Generators;

use Zhp\DataCollector\Interfaces\RequestIdGeneratorInterface;

class RequestIdGenerator implements RequestIdGeneratorInterface
{
    public function generate()
    {
        return md5(serialize($_SERVER) . microtime());
    }
}
