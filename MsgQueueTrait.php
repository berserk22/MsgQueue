<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\MsgQueue;

use Core\Traits\App;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\MsgQueue\Manager\MsgManager;

trait MsgQueueTrait {

    use App;

    /**
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getDashboardMsgQueueRouter(): mixed {
        return $this->getContainer()->get('MsgQueue\DashboardRouter');
    }

    /**
     * @return MsgManager
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getMsgManager(): MsgManager {
        return $this->getContainer()->get('MsgQueue\Manager');
    }

}
