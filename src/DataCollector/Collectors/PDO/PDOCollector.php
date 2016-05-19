<?php

namespace Zhp\DataCollector\Collectors\PDO;

use PDO;
use Zhp\DataCollector\Collectors\AbstractCollector;
use Zhp\DataCollector\Collectors\TimeDataCollector;

class PDOCollector extends AbstractCollector
{
    protected $connections = [];

    protected $timeCollector;

    protected $renderSqlWithParams = true;

    protected $sqlQuotationChar = '<>';

    protected $collectorName = 'pdo';

    public function __construct(TraceablePDO $pdo = null, TimeDataCollector $timeCollector = null)
    {
        $this->timeCollector = $timeCollector;
        if ($pdo !== null) {
            $this->addConnection($pdo, 'default');
        }
    }

    public function setRenderSqlWithParams($enabled = true, $quotationChar = '<>')
    {
        $this->renderSqlWithParams = $enabled;
        $this->sqlQuotationChar = $quotationChar;
    }

    public function isSqlRenderedWithParams()
    {
        return $this->renderSqlWithParams;
    }

    public function getSqlQuotationChar()
    {
        return $this->sqlQuotationChar;
    }

    public function addConnection(TraceablePDO $pdo, $name = null)
    {
        if ($name === null) {
            $name = spl_object_hash($pdo);
        }
        $this->connections[$name] = $pdo;
    }

    public function getConnections()
    {
        return $this->connections;
    }

    public function collect()
    {
        $data = [
            'nb_statements' => 0,
            'nb_failed_statements' => 0,
            'accumulated_duration' => 0,
            'memory_usage' => 0,
            'peak_memory_usage' => 0,
            'statements' => []
        ];

        foreach ($this->connections as $name => $pdo) {
            $pdodata = $this->collectPDO($pdo, $this->timeCollector);
            $data['nb_statements'] += $pdodata['nb_statements'];
            $data['nb_failed_statements'] += $pdodata['nb_failed_statements'];
            $data['accumulated_duration'] += $pdodata['accumulated_duration'];
            $data['memory_usage'] += $pdodata['memory_usage'];
            $data['peak_memory_usage'] = max($data['peak_memory_usage'], $pdodata['peak_memory_usage']);
            $data['connection_status'] = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
            $data['statements'] = array_merge($data['statements'],
                array_map(function ($s) use ($name) { $s['connection'] = $name; return $s; }, $pdodata['statements']));
        }

        $data['accumulated_duration_str'] = $this->getDataFormatter()->formatDuration($data['accumulated_duration']);
        $data['memory_usage_str'] = $this->getDataFormatter()->formatBytes($data['memory_usage']);
        $data['peak_memory_usage_str'] = $this->getDataFormatter()->formatBytes($data['peak_memory_usage']);

        return $data;
    }

    protected function collectPDO(TraceablePDO $pdo, TimeDataCollector $timeCollector = null)
    {
        $stmts = [];
        foreach ($pdo->getExecutedStatements() as $stmt) {
            $stmts[] = [
                'sql' => $this->renderSqlWithParams ? $stmt->getSqlWithParams($this->sqlQuotationChar) : $stmt->getSql(),
                'row_count' => $stmt->getRowCount(),
                'stmt_id' => $stmt->getPreparedId(),
                'prepared_stmt' => $stmt->getSql(),
                'params' => (object) $stmt->getParameters(),
                'duration' => $stmt->getDuration(),
                'duration_str' => $this->getDataFormatter()->formatDuration($stmt->getDuration()),
                'memory' => $stmt->getMemoryUsage(),
                'memory_str' => $this->getDataFormatter()->formatBytes($stmt->getMemoryUsage()),
                'end_memory' => $stmt->getEndMemory(),
                'end_memory_str' => $this->getDataFormatter()->formatBytes($stmt->getEndMemory()),
                'is_success' => $stmt->isSuccess(),
                'error_code' => $stmt->getErrorCode(),
                'error_message' => $stmt->getErrorMessage()
            ];

            if ($timeCollector !== null) {
                $timeCollector->addMeasure($stmt->getSql(), $stmt->getStartTime(), $stmt->getEndTime());
            }
        }

        return [
            'nb_statements' => count($stmts),
            'nb_failed_statements' => count($pdo->getFailedExecutedStatements()),
            'accumulated_duration' => $pdo->getAccumulatedStatementsDuration(),
            'accumulated_duration_str' => $this->getDataFormatter()->formatDuration($pdo->getAccumulatedStatementsDuration()),
            'memory_usage' => $pdo->getMemoryUsage(),
            'memory_usage_str' => $this->getDataFormatter()->formatBytes($pdo->getPeakMemoryUsage()),
            'peak_memory_usage' => $pdo->getPeakMemoryUsage(),
            'peak_memory_usage_str' => $this->getDataFormatter()->formatBytes($pdo->getPeakMemoryUsage()),
            'statements' => $stmts
        ];
    }
}
