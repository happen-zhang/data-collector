<?php

namespace Zhp\DataCollector\Collectors\PDO;

use Exception;

class TracedStatement
{
    protected $sql;

    protected $rowCount;

    protected $parameters;

    protected $startTime;

    protected $endTime;

    protected $duration;

    protected $startMemory;

    protected $endMemory;

    protected $memoryDelta;

    protected $exception;

    public function __construct($sql, array $params = [], $preparedId = null)
    {
        $this->sql = $sql;
        $this->parameters = $this->checkParameters($params);
        $this->preparedId = $preparedId;
    }

    public function start($startTime = null, $startMemory = null)
    {
        $this->startTime = $startTime ?: microtime(true);
        $this->startMemory = $startMemory ?: memory_get_usage(true);
    }

    public function end(Exception $exception = null, $rowCount = 0, $endTime = null, $endMemory = null)
    {
        $this->endTime = $endTime ?: microtime(true);
        $this->duration = $this->endTime - $this->startTime;
        $this->endMemory = $endMemory ?: memory_get_usage(true);
        $this->memoryDelta = $this->endMemory - $this->startMemory;
        $this->exception = $exception;
        $this->rowCount = $rowCount;
    }

    public function checkParameters($params)
    {
        foreach ($params as &$param) {
            if (!mb_check_encoding($param, 'UTF-8')) {
                $param = '[BINARY DATA]';
            }
        }

        return $params;
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function getSqlWithParams($quotationChar = '<>')
    {
        if (($l = strlen($quotationChar)) > 1) {
            $quoteLeft = substr($quotationChar, 0, $l / 2);
            $quoteRight = substr($quotationChar, $l / 2);
        } else {
            $quoteLeft = $quoteRight = $quotationChar;
        }

        $sql = $this->sql;
        foreach ($this->parameters as $k => $v) {
            $v = "$quoteLeft$v$quoteRight";
            if (!is_numeric($k)) {
                $sql = str_replace($k, $v, $sql);
            } else {
                $p = strpos($sql, '?');
                $sql = substr($sql, 0, $p) . $v. substr($sql, $p + 1);
            }
        }
        return $sql;
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }

    public function getParameters()
    {
        $params = [];
        foreach ($this->parameters as $name => $param) {
            $params[$name] = htmlentities($param, ENT_QUOTES, 'UTF-8', false);
        }
        return $params;
    }

    public function getPreparedId()
    {
        return $this->preparedId;
    }

    public function isPrepared()
    {
        return $this->preparedId !== null;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function getStartMemory()
    {
        return $this->startMemory;
    }

    public function getEndMemory()
    {
        return $this->endMemory;
    }

    public function getMemoryUsage()
    {
        return $this->memoryDelta;
    }

    public function isSuccess()
    {
        return $this->exception === null;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function getErrorCode()
    {
        return $this->exception !== null ? $this->exception->getCode() : 0;
    }

    public function getErrorMessage()
    {
        return $this->exception !== null ? $this->exception->getMessage() : '';
    }
}
