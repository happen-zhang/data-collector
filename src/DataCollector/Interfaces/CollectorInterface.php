<?php

namespace Zhp\DataCollector\Interfaces;

interface CollectorInterface
{
    /**
     * 收集器名称
     * @return [type] [description]
     */
    public function getName();

    /**
     * 设置收集器名称
     * @param [type] $name [description]
     */
    public function setName($name);

    /**
     * 收集动作
     * @return [type] [description]
     */
    public function collect();
}
