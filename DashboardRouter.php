<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\MsgQueue;

use Core\Module\Router;
use Modules\MsgQueue\Controller\DashboardController;

class DashboardRouter extends Router {

    /**
     * @var string
     */
    public string $routerType = "dashboard_msg";

    /**
     * @var string
     */
    public string $router = "/dashboard/msg";

    /**
     * @var array|string[][]
     */
    public array $mapForUriBuilder = [
        'list' => [
            'callback' => 'list',
            'pattern' =>'',
            'method' => ['GET']
        ],
        'view' => [
            'callback' => 'view',
            'pattern' =>'/view/{msgId:int}',
            'method' => ['POST', 'GET']
        ],
        'redirect'=>[
            'callback' => 'redirect',
            'pattern' =>'/redirect/{msgId:int}',
            'method' => ['POST', 'GET']
        ],
        'remove'=>[
            'callback' => 'remove',
            'pattern' =>'/remove-{msgId:int}',
            'method' => ['DELETE']
        ],
    ];

    public string $controller = DashboardController::class;
}
