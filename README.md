# data-collector

Simple data collector for php application, inspiration comes from maximebf/debugbar.

## Install

```
$ composer require zhp/data-collector
```

## Usage

```php
require 'vendor/autoload.php';

use Zhp\DataCollector\DataCollector;
use Zhp\DataCollector\Collectors\MemoryCollector;
use Zhp\DataCollector\Storages\FileStorage;

$dataCollector = new DataCollector;
$dataCollector->setStorage(new FileStorage(__DIR__ . '/logs'));
$dataCollector->addCollector(new MemoryCollector);

$dataCollector->getData();
```

## Todo

more...
