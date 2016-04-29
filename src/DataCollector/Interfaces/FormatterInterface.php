<?php

namespace Zhp\DataCollector\Interfaces;

interface FormatterInterface
{
    function formatVar($data);

    function formatDuration($seconds);

    function formatBytes($size, $precision = 2);
}
