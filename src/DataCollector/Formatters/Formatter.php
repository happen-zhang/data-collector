<?php

namespace Zhp\DataCollector\Formatters;

use Zhp\DataCollector\Interfaces\FormatterInterface;

class Formatter implements FormatterInterface
{
    public function formatVar($data)
    {
        return $data;
    }

    public function formatDuration($seconds)
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000) . 'μs';
        } elseif ($seconds < 1) {
            return round($seconds * 1000, 2) . 'ms';
        }

        return round($seconds, 2) . 's';
    }

    public function formatBytes($size, $precision = 2)
    {
        if ($size === 0 || $size === null) {
            return '0B';
        }

        $base = log($size) / log(1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }
}
