<?php
namespace Pentagonal\Phalcon\Application\Web\Service;

use Pentagonal\Phalcon\Application\Globals\Plugin\Auth;
use Pentagonal\Phalcon\Service;

if (!isset($this) || ! $this instanceof Service) {
    return;
}

return $this
    ->di
    ->set('auth', Auth::class);
