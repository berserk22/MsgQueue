<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\MsgQueue;

use DI\DependencyException;
use DI\NotFoundException;

class MsgQueue {

    use MsgQueueTrait;

    public function __construct($app) {
        $this->setApp($app->getApp());
        $this->setContainer($app->getContainer());
    }

    /**
     * @var array|string[]
     */
    protected array $events = [
        'mail' => '\Modules\MsgQueue\Events\Mail',
    ];

    /**
     * @param string $event
     * @param mixed $message
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function setMessage(string $event, mixed $message): void {
        if (isset($this->events[$event])){
            $class = new $this->events[$event];
            if (method_exists($class, 'setConfig')){
                $class->setConfig($this->getContainer()->get("config")->getSetting("queue"));
            }
            $class->send($message);
        }
    }

}
