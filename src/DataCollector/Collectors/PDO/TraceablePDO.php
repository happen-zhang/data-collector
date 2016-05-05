<?php

namespace Zhp\DataCollector\Collectors\PDO;

use PDO;
use PDOException;
use Zhp\DataCollector\Collectors\PDO\TraceablePDOStatement;

class TraceablePDO extends PDO
{
    protected $pdo;

    protected $executedStatements = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, [TraceablePDOStatement::class, [$this]]);
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function errorCode()
    {
        return $this->pdo->errorCode();
    }

    public function errorInfo()
    {
        return $this->pdo->errorInfo();
    }

    public function exec($sql)
    {
        return $this->profileCall('exec', $sql, func_get_args());
    }

    public function getAttribute($attr)
    {
        return $this->pdo->getAttribute($attr);
    }

    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    public function lastInsertId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    public function prepare($sql, $driver_options = [])
    {
        return $this->pdo->prepare($sql, $driver_options);
    }

    public function query($sql)
    {
        return $this->profileCall('query', $sql, func_get_args());
    }

    public function quote($expr, $parameter_type = PDO::PARAM_STR)
    {
        return $this->pdo->quote($expr, $parameter_type);
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    public function setAttribute($attr, $value)
    {
        return $this->pdo->setAttribute($attr, $value);
    }

    protected function profileCall($method, $sql, array $args)
    {
        $trace = new TracedStatement($sql);
        $trace->start();

        $ex = null;
        try {
            $result = call_user_func_array([$this->pdo, $method], $args);
        } catch (PDOException $e) {
            $ex = $e;
        }

        if ($this->pdo->getAttribute(PDO::ATTR_ERRMODE) !== PDO::ERRMODE_EXCEPTION && $result === false) {
            $error = $this->pdo->errorInfo();
            $ex = new PDOException($error[2], $error[0]);
        }

        $trace->end($ex);
        $this->addExecutedStatement($trace);

        if ($this->pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION && $ex !== null) {
            throw $ex;
        }

        return $result;
    }

    public function addExecutedStatement(TracedStatement $stmt)
    {
        $this->executedStatements[] = $stmt;
    }

    public function getAccumulatedStatementsDuration()
    {
        return array_reduce($this->executedStatements, function ($v, $s) { return $v + $s->getDuration(); });
    }

    public function getMemoryUsage()
    {
        return array_reduce($this->executedStatements, function ($v, $s) { return $v + $s->getMemoryUsage(); });
    }

    public function getPeakMemoryUsage()
    {
        return array_reduce($this->executedStatements, function ($v, $s) { $m = $s->getEndMemory(); return $m > $v ? $m : $v; });
    }

    public function getExecutedStatements()
    {
        return $this->executedStatements;
    }

    public function getFailedExecutedStatements()
    {
        return array_filter($this->executedStatements, function ($s) { return !$s->isSuccess(); });
    }

    public function __get($name)
    {
        return $this->pdo->$name;
    }

    public function __set($name, $value)
    {
        $this->pdo->$name = $value;
    }

    public function __call($name, $args)
    {
        return call_user_func_array([$this->pdo, $name], $args);
    }
}
