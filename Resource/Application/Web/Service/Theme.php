<?php
namespace Pentagonal\Phalcon\Application\Web\Service;

use Pentagonal\Phalcon\Application\Globals\Library\Theme;
use Pentagonal\Phalcon\Service;

if (!isset($this) || ! $this instanceof Service) {
    return;
}

$this
    ->di
    ->setShared('theme', function () {
        $themes = new Theme(dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . 'themes');
        return $themes;
    });
