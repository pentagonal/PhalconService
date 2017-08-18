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
$group->setPrefix('/' . ControllerBase::getManagePrefix());
$group->add(
    '(?:/(?:([a-zA-Z0-9][a-zA-Z0-9\_]{0,})(?:/([a-zA-Z0-9\_\-]+)?)?)?)?',
    [
        'module'    => 'backend',
        'controller' => 'Manage',
        'action'     => 'slug',
        'path'      => 1,
        'slug'      => 2
    ],
    ['GET', 'POST']
);

$router->mount($group);
