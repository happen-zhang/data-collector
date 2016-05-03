<?php

namespace Zhp\DataCollector\Collectors;

use Exception;

class ExceptionsCollector extends AbstractCollector
{
    protected $exceptions = [];

    protected $chainExceptions = false;

    public function addException(Exception $e)
    {
        $this->exceptions[] = $e;

        if ($this->chainExceptions && $previous = $e->getPrevious()) {
            $this->addException($previous);
        }
    }

    public function setChainExceptions($chainExceptions = true)
    {
        $this->chainExceptions = $chainExceptions;
    }

    public function getExceptions()
    {
        return $this->exceptions;
    }

    public function collect()
    {
        return [
            'count' => count($this->exceptions),
            'exceptions' => array_map([$this, 'formatExceptionData'], $this->exceptions)
        ];
    }

    public function formatExceptionData(Exception $e)
    {
        $filePath = $e->getFile();
        if ($filePath && file_exists($filePath)) {
            $lines = file($filePath);
            $start = $e->getLine() - 4;
            $lines = array_slice($lines, $start < 0 ? 0 : $start, 7);
        } else {
            $lines = ["Cannot open the file ($filePath) in which the exception occurred "];
        }

        return [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $filePath,
            'line' => $e->getLine(),
            'surrounding_lines' => $lines
        ];
    }

    public function getName()
    {
        return 'exceptions';
    }
}
