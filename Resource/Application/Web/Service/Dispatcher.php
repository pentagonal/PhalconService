<?php
namespace Pentagonal\Phalcon\Application\Web\Service;

use Pentagonal\Phalcon\Service;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;

if (!isset($this) || !$this instanceof Service) {
    return;
}

return $this
    ->di
    ->setShared('dispatcher', function () {
        $dispatcher = new Dispatcher();
        /**
         * @var Router $router
         */
        $router = $this['router'];
        $defaults = $router->getDefaults();
        $dispatcher->setControllerSuffix('Controller');
        $dispatcher->setActionSuffix('Action');
        $dispatcher->setDefaultAction($defaults['action']);
        $dispatcher->setDefaultController($defaults['controller']);
        $dispatcher->setDefaultNamespace($defaults['namespace']);
        /**
         * @var Manager $eventsManager
         */
        $eventsManager = $this['eventsManager'];
        $dispatcher->setEventsManager($eventsManager);

        // attach auth
        $eventsManager->attach(
            'dispatch:beforeExecuteRoute',
            $this['auth']
        );

        return $dispatcher;
    });
