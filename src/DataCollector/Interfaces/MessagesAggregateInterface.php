<?php

namespace Zhp\DataCollector\Interfaces;

interface MessagesAggregateInterface
{
    public function addMessage($message, $label = 'info', $isString = true);

    public function getMessages();
}
