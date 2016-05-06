<?php

namespace Zhp\DataCollector\Storages;

use PDO;
use Zhp\DataCollector\Interfaces\StorageInterface;

class PdoStorage implements StorageInterface
{
    protected $pdo;

    protected $tableName;

    protected $sqlQueries = [
        'save' => "INSERT INTO %tablename% (id, data, meta_utime, meta_datetime, meta_uri, meta_ip, meta_method) VALUES (?, ?, ?, ?, ?, ?, ?)",
        'get' => "SELECT data FROM %tablename% WHERE id = ?",
        'find' => "SELECT data FROM %tablename% %where% ORDER BY meta_datetime DESC LIMIT %limit% OFFSET %offset%",
        'clear' => "DELETE FROM %tablename%"
    ];

    public function __construct(PDO $pdo, $tableName = 'data_collector', array $sqlQueries = [])
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->setSqlQueries($sqlQueries);
    }

    public function setSqlQueries(array $queries)
    {
        $this->sqlQueries = array_merge($this->sqlQueries, $queries);
    }

    public function save($id, $data)
    {
        $sql = $this->getSqlQuery('save');
        $stmt = $this->pdo->prepare($sql);
        $meta = $data['__meta'];
        $stmt->execute(array($id, serialize($data), $meta['utime'], $meta['datetime'], $meta['uri'], $meta['ip'], $meta['method']));
    }

    public function get($id)
    {
        $sql = $this->getSqlQuery('get');
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        if (($data = $stmt->fetchColumn(0)) !== false) {
            return unserialize($data);
        }
        return null;
    }

    public function find(array $filters = [], $max = 20, $offset = 0)
    {
        $where = [];
        $params = [];
        foreach ($filters as $key => $value) {
            $where[] = "meta_$key = ?";
            $params[] = $value;
        }
        if (count($where)) {
            $where = " WHERE " . implode(' AND ', $where);
        } else {
            $where = '';
        }

        $sql = $this->getSqlQuery('find', [
            'where' => $where,
            'offset' => $offset,
            'limit' => $max
        ]);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $data = unserialize($row['data']);
            $results[] = $data['__meta'];
            unset($data);
        }

        return $results;
    }

    public function last()
    {
        return $this->find([], 1);
    }

    public function clear()
    {
        $this->pdo->exec($this->getSqlQuery('clear'));
    }

    protected function getSqlQuery($name, array $vars = [])
    {
        $sql = $this->sqlQueries[$name];
        $vars = array_merge(['tablename' => $this->tableName], $vars);
        foreach ($vars as $k => $v) {
            $sql = str_replace("%$k%", $v, $sql);
        }
        return $sql;
    }
}
