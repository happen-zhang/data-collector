<?php

namespace Zhp\DataCollector\Collectors;

class MemoryCollector extends AbstractCollector
{
    protected $peakUsage = 0;

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
        return array(
            'peak_usage' => $this->peakUsage,
            'peak_usage_str' => $this->getDataFormatter()->formatBytes($this->peakUsage)
        );
    }

    public function getName()
    {
        return 'memory';
    }
}
