<?php
namespace Pentagonal\Phalcon\Application\Web\Service;

use Pentagonal\Phalcon\Service;
use Phalcon\Application;

if (!isset($this) || ! $this instanceof Service) {
    return;
}

/**
 * @var Application $app
 */
$app = $this->di['app'];
$app->registerModules([
    'frontend' => [
        'className' => 'Pentagonal\\Phalcon\\Application\\Web\\Module\\FrontEndModule',
        'path'      => dirname(__DIR__) .'/Modules/FrontEndModule.php',
    ],
    'backend' => [
        'className' => 'Pentagonal\\Phalcon\\Application\\Web\\Module\\BackendModule',
        'path'      => dirname(__DIR__) .'/Modules/BackendModule.php',
    ],
]);
$app->setDefaultModule('frontend');

require_once __DIR__ . '/Acl.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Dispatcher.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/Theme.php';
require_once __DIR__ . '/ViewCache.php';
