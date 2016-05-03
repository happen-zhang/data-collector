<?php

namespace Zhp\DataCollector\Collectors;

class LocalizationCollector extends AbstractCollector
{
    public function getLocale()
    {
        return setlocale(LC_ALL, 0);
    }

    public function getDomain()
    {
        return textdomain(null);
    }

    public function collect()
    {
        return [
            'locale' => $this->getLocale(),
            'domain' => $this->getDomain(),
        ];
    }

    public function getName()
    {
        return 'localization';
    }
}
