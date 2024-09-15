<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\MsgQueue;

use Core\Module\Provider;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\Database\MigrationCollection;
use Modules\MsgQueue\Console\SendMail;
use Modules\MsgQueue\Db\Schema;
use Modules\MsgQueue\Manager\MsgManager;
use Modules\View\PluginManager;
use Modules\View\ViewManager;

class ServiceProvider extends Provider {

    private string $dashboardRouter = "MsgQueue\DashboardRouter";

    /**
     * @var array|string[]
     */
    protected array $plugins = [
        'getMsgContent' => '\Modules\MsgQueue\Plugins\GetMsgContent',
    ];

    /**
     * @return string[]
     */
    public function console(): array {
        return [
            SendMail::class
        ];
    }
    public function init(): void {
        $container = $this->getContainer();
        if (!$container->has('MsgQueue\Queue')){
            $container->set('MsgQueue\Queue', function(){
                return new MsgQueue($this);
            });
        }

        if (!$container->has($this->dashboardRouter)){
            $container->set($this->dashboardRouter, function(){
                return new DashboardRouter($this);
            });
        }
    }

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function afterInit(): void {
        $container = $this->getContainer();
        if ($container->has('Modules\Database\ServiceProvider::Migration::Collection')) {
            /* @var $databaseMigration MigrationCollection */
            $container->get('Modules\Database\ServiceProvider::Migration::Collection')->add(new Schema($this));
        }

        if ($container->has('ViewManager::View')){
            /** @var $viewer ViewManager */
            $viewer = $container->get('ViewManager::View');
            $plugins = function(){
                $pluginManager = new PluginManager();
                $pluginManager->addPlugins($this->plugins);
                return $pluginManager->getPlugins();
            };
            $viewer->setPlugins($plugins());
        }

        if (!$container->has('MsgQueue\Manager')) {
            $this->getContainer()->set('MsgQueue\Manager', function(){
                $manager = new MsgManager($this);
                return $manager->initEntity();
            });
        }
    }

    /**
     * @return void
     */
    public function boot(): void {
        $container = $this->getContainer();

        $container->set('Modules\MsgQueue\Controller\DashboardController', function(){
            return new Controller\DashboardController($this);
        });

    }

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function register(): void {
        $container = $this->getContainer();
        if ($container->has($this->dashboardRouter)){
            $container->get($this->dashboardRouter)->init();
        }

    }

}
