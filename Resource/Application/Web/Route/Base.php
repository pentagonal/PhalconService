<?php
namespace Pentagonal\Phalcon\Application\Web\Route;

use Pentagonal\Phalcon\Application\Web\Controller\ControllerBase;
use Pentagonal\Phalcon\Service;
use Phalcon\Mvc\Router;

if (!isset($this) || ! $this instanceof Service) {
    return;
}

/**
 * @var Router $router
 */
$router = $this->router;
$group = new Router\Group();
$group->setPrefix('/(?!' . ControllerBase::getManagePrefix() . ')');
$group->add(
    '',
    [
        'module'    => 'frontend',
        'controller' => 'index',
        'action'     => 'index'
    ],
    ['GET', 'POST']
);
$group->add(
    '(?:/+)?robots.txt',
    [
        'module'    => 'frontend',
        'controller' => 'index',
        'action'     => 'robots'
    ],
    ['GET', 'POST', 'PUT', "PATCH", "DELETE", 'OPTIONS', "HEAD"]
);

$group->add(
    '{slug:[a-zA-Z0-9][a-zA-Z0-9\_\-\/]{0,}}?',
    [
        'module'    => 'frontend',
        'controller' => 'Index',
        'action'     => 'slug',
        'params'     => 1
    ],
    ['GET', 'POST']
);

$router->mount($group);
