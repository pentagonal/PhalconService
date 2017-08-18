<?php
declare(strict_types=1);

namespace Pentagonal\Phalcon\Application\Web\Route;

use Pentagonal\Phalcon\Service;

if ( !isset($this) || ! $this instanceof Service) {
    return;
}

require_once __DIR__ . '/Base.php';
require_once __DIR__ . '/Manage.php';
