<?php

namespace Zhp\DataCollector\Collectors;

class MemoryCollector extends AbstractCollector
{
    protected $peakUsage = 0;

    protected $collectorName = 'memory';

    public function getPeakUsage()
    {
        return $this->peakUsage;
    }

    public function updatePeakUsage()
    {
        $this->peakUsage = memory_get_peak_usage(true);
    }

    public function collect()
    {
        $this->updatePeakUsage();
        return [
            'peak_usage' => $this->peakUsage,
            'peak_usage_str' => $this->getDataFormatter()->formatBytes($this->peakUsage)
        ];
    }
}
