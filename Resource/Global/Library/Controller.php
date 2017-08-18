<?php
declare(strict_types=1);

namespace Pentagonal\Phalcon\Application\Globals\Library;

use Pentagonal\Phalcon\Application\Globals\Plugin\Auth;
use Phalcon\Acl\Adapter\Memory;

/**
 * Class Controller
 * @package Pentagonal\Phalcon\Application\Globals\Library
 *
 * @property-read Auth $auth
 * @property-read Memory $acl
 */
class Controller extends \Phalcon\Mvc\Controller implements \ArrayAccess
{
    /**
     * @param string $path
     *
     * @return string
     */
    public function getBaseUri(string $path = '') : string
    {
        $uri = $this->url->getStaticBaseUri()?: $this->url->getBaseUri();
        $uri = rtrim($uri, '/') . '/';
        $path = ltrim($path, '/');
        if (trim($path) == '' || $path == '/') {
            return $uri;
        }

        return $uri . $path;
    }

    public function offsetUnset($offset)
    {
        unset($this->di[$offset]);
    }

    public function offsetExists($offset)
    {
        return isset($this->di[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->di[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->di[$offset] = $value;
    }
}
