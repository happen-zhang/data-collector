<?php

namespace Zhp\DataCollector\Collectors;

use Psr\Log\AbstractLogger;
use Zhp\DataCollector\Interfaces\CollectorInterface;
use Zhp\DataCollector\Interfaces\MessagesAggregateInterface;
use Zhp\DataCollector\Interfaces\FormatterInterface;

class MessagesCollector extends AbstractLogger implements CollectorInterface, MessagesAggregateInterface
{
    protected $name;

    protected $messages = [];

    protected $aggregates = [];

    protected $dataFormater;

    public function __construct($name = 'messages')
    {
        $this->name = $name;
    }

    public function setDataFormatter(FormatterInterface $formater)
    {
        $this->dataFormater = $formater;
        return $this;
    }

    public function getDataFormatter()
    {
        if ($this->dataFormater === null) {
            $this->dataFormater = AbstractCollector::getDefaultDataFormatter();
        }

        return $this->dataFormater;
    }

    public function aggregate(MessagesAggregateInterface $messages)
    {
        $this->aggregates[] = $messages;
    }

    public function addMessage($message, $label = 'info', $isString = true)
    {
        if (!is_string($message)) {
            $message = $this->getDataFormatter()->formatVar($message);
            $isString = false;
        }

        $this->messages[] = [
            'message' => $message,
            'is_string' => $isString,
            'label' => $label,
            'time' => microtime(true)
        ];
    }

    public function getMessages()
    {
        $messages = $this->messages;
        foreach ($this->aggregates as $collector) {
            $msgs = array_map(function ($m) use ($collector) {
                $m['collector'] = $collector->getName();
                return $m;
            }, $collector->getMessages());
            $messages = array_merge($messages, $msgs);
        }

        usort($messages, function ($a, $b) {
            if ($a['time'] === $b['time']) {
                return 0;
            }

            return $a['time'] < $b['time'] ? -1 : 1;
        });

        return $messages;
    }

    public function log($level, $message, array $context = array())
    {
        $this->addMessage($message, $level);
    }

    public function clear()
    {
        $this->messages = [];
    }

    public function collect()
    {
        $messages = $this->getMessages();
        return [
            'count' => count($messages),
            'messages' => $messages
        ];
    }

    public function getName()
    {
        return $this->name;
    }
}
