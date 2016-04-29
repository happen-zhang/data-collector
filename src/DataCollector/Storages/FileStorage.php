<?php

namespace Zhp\DataCollector\Storages;

use DirectoryIterator;
use Zhp\DataCollector\Interfaces\StorageInterface;

class FileStorage implements StorageInterface
{
    protected $dirname;

    public function __construct($dirname)
    {
        $this->dirname = rtrim($dirname, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function save($id, $data)
    {
        if (!file_exists($this->dirname)) {
            mkdir($this->dirname, 0777, true);
        }

        file_put_contents($this->makeFilename($id), json_encode($data));
    }

    public function get($id)
    {
        return json_decode(file_get_contents($this->makeFilename($id)), true);
    }

    public function find(array $filters = [], $max = 20, $offset = 0)
    {
        $files = array();
        foreach (new DirectoryIterator($this->dirname) as $file) {
            if ($file->getExtension() == 'json') {
                $files[] = array(
                    'time' => $file->getMTime(),
                    'id' => $file->getBasename('.json')
                );
            }
        }

        usort($files, function ($a, $b) {
            return $a['time'] < $b['time'];
        });

        $results = array();
        $i = 0;
        foreach ($files as $file) {
            if ($i++ < $offset && empty($filters)) {
                $results[] = null;
                continue;
            }

            $data = $this->get($file['id']);
            $meta = $data['__meta'];
            unset($data);

            if ($this->filter($meta, $filters)) {
                $results[] = $meta;
            }

            if (count($results) >= ($max + $offset)) {
                break;
            }
        }

        return array_slice($results, $offset, $max);
    }

    protected function filter($meta, $filters)
    {
        foreach ($filters as $key => $value) {
            if (!isset($meta[$key]) || fnmatch($value, $meta[$key]) === false) {
                return false;
            }
        }

        return true;
    }

    public function clear()
    {
        foreach (new DirectoryIterator($this->dirname) as $file) {
            if (substr($file->getFilename(), 0, 1) !== '.') {
                unlink($file->getPathname());
            }
        }
    }

    public function makeFilename($id)
    {
        return $this->dirname . basename($id). ".json";
    }
}
