<?php

namespace Zhp\DataCollector\Collectors;

class RequestDataCollector extends AbstractCollector
{
    public function collect()
    {
        $vars = ['_GET', '_POST', '_SESSION', '_COOKIE', '_SERVER'];
        $data = [];

        foreach ($vars as $var) {
            if (isset($GLOBALS[$var])) {
                $data["$" . $var] = $this->getDataFormatter()->formatVar($GLOBALS[$var]);
            }
        }

        return $data;
    }

    public function getName()
    {
        return 'request';
    }
}
