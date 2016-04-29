<?php

namespace Zhp\DataCollector\Interfaces;

interface StorageInterface
{
    public function save($id, $data);

    public function get($id);

    public function find(array $filters = [], $max = 20, $offset = 0);

    public function clear();
}
