<?php

namespace Modules\MsgQueue\Manager;

use Core\Traits\App;
use DI\DependencyException;
use DI\NotFoundException;

class MsgManager {

    use App;

    private string $mailQueue = "MsgQueue\Mail";

    public function initEntity(): static {
        if (!$this->getContainer()->has($this->mailQueue)) {
            $this->getContainer()->set($this->mailQueue, function () {
                return 'Modules\MsgQueue\Db\Models\MailQueue';
            });
        }
        return $this;
    }

    /**
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getMsgMailEntity(): mixed {
        return $this->getContainer()->get($this->mailQueue);
    }
}
