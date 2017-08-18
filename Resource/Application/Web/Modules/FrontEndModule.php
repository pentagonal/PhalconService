<?php
namespace Pentagonal\Phalcon\Application\Web\Module;

use Phalcon\DiInterface;
use Phalcon\Mvc\User\Module;

/**
 * Class FrontEndModule
 * @package Pentagonal\Phalcon\Application\Web\Module
 */
class FrontEndModule extends Module
{
    protected $isBackend = false;

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
