<?php
namespace Pentagonal\Phalcon\Application\Web\Module;

use Phalcon\DiInterface;
use Phalcon\Mvc\User\Module;

/**
 * Class BackendModule
 * @package Pentagonal\Phalcon\Application\Web\Module
 */
class BackendModule extends Module
{
    protected $isBackend = true;

    /**
     * @param DiInterface|null $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        // pass
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'ServiceView.php';
    }
}
