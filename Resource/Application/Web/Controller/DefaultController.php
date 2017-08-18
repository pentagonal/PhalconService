<?php
namespace Pentagonal\Phalcon\Application\Web\Controller;

use Pentagonal\Phalcon\Service;
use Phalcon\Http\Response;

/**
 * Class DefaultController
 * @package Pentagonal\Phalcon\Application\Web\Controller
 *
 * @property-read Service $service
 */
class DefaultController extends ControllerBase
{
    /**
     * Backward Compatibility
     *
     * @return \Phalcon\Http\Response
     */
    public function indexAction() : Response
    {
        return $this->showNotFound();
    }
}
