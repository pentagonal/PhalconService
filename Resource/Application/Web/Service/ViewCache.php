<?php
namespace Pentagonal\Phalcon\Application\Web\Service;

use Pentagonal\Phalcon\Service;
use Phalcon\Cache\Backend;

if (!isset($this) || ! $this instanceof Service) {
    return;
}

return $this
    ->di
    ->setShared('viewCache', function () {
        /**
         * @var Backend $cache
         */
        $cache = clone $this['cache'];
        /**
         * @var array $options
         */
        $options = (array) $cache->getOptions();
        if (!empty($options['adapter']) && $options['adapter'] == 'file'
            || stripos(get_class($cache), 'file')
        ) {
            $options['cacheDir'] .= 'viewCache' . DIRECTORY_SEPARATOR;
            $options['cacheDir'] = realpath($options['cacheDir']) ?: $options['cacheDir'];
            $options['cacheDir'] = rtrim($options['cacheDir'], '\\/') . DIRECTORY_SEPARATOR;

            if (! is_dir($options['cacheDir'])) {
                @mkdir($options['cacheDir'], 0777, true);
            }
            if ($this['config']['environment'] == 'development'
                || ! empty($options['compileAlways'])
                || isset($options['lifetime'])
                   && (
                       ! is_numeric($options['lifetime'])
                       || $options['lifetime'] < 1
                   )
            ) {
                $path = $options['cacheDir'];
                array_map(function ($c) {
                    if (is_file($c)) {
                        unlink($c);
                    }
                }, glob($path . '/*'));
            }
        }

        $options['key'] = 'viewCache';
        $cache->setOptions($options);
        return $cache;
    });
