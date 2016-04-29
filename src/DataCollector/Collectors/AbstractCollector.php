<?php

namespace Zhp\DataCollector\Collectors;

use Zhp\DataCollector\Interfaces\CollectorInterface;
use Zhp\DataCollector\Interfaces\FormatterInterface;
use Zhp\DataCollector\Formatters\Formatter;

abstract class AbstractCollector implements CollectorInterface
{
    private static $defaultDataFormatter;

    protected $dataFormater;

    public static function setDefaultDataFormatter(FormatterInterface $formater)
    {
        self::$defaultDataFormatter = $formater;
    }

    public static function getDefaultDataFormatter()
    {
        if (self::$defaultDataFormatter === null) {
            self::$defaultDataFormatter = new Formatter();
        }

        return self::$defaultDataFormatter;
    }

    public function setDataFormatter(FormatterInterface $formater)
    {
        $this->dataFormater = $formater;
        return $this;
    }

    public function getDataFormatter()
    {
        if ($this->dataFormater === null) {
            $this->dataFormater = self::getDefaultDataFormatter();
        }
        return $this->dataFormater;
    }

    public function formatVar($var)
    {
        return $this->getDataFormatter()->formatVar($var);
    }

    public function formatDuration($seconds)
    {
        return $this->getDataFormatter()->formatDuration($seconds);
    }

    public function formatBytes($size, $precision = 2)
    {
        return $this->getDataFormatter()->formatBytes($size, $precision);
    }
}
