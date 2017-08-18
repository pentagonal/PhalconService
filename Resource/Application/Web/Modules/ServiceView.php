<?php
namespace Pentagonal\Phalcon\Application\Web\Service;

use Pentagonal\Phalcon\Application\Globals\Library\Hook;
use Pentagonal\Phalcon\Application\Globals\Library\StringSanity;
use Pentagonal\Phalcon\Application\Globals\Library\Theme;
use Pentagonal\Phalcon\Application\Globals\Model\Option;
use Pentagonal\Phalcon\Service;
use Phalcon\Config;
use Phalcon\DiInterface;
use Phalcon\Escaper;
use Phalcon\Events\Manager;
use Phalcon\Mvc\User\Module;
use Phalcon\Mvc\View;
use Phalcon\Tag;

if (!isset($this) || ! $this instanceof Module
    || (!isset($di)  || ! $di instanceof DiInterface)
) {
    return;
}

$isBackend = isset($this->isBackend) && $this->isBackend;

/**
 * @return View
 */
$di->set('view', function () use ($isBackend) {
    $view = new View();
    /**
     * @var Theme $theme
     */
    $theme = $this['theme'];
    $defaultTheme = $theme->getActiveTheme();
    /**
     * @var Config $currentTheme
     */
    $currentTheme = $defaultTheme;
    if ($currentTheme) {
        $option = Option::find(
            [
                'options_name = :active_theme:',
                'bind' => [
                    'active_theme' => 'theme:active'
                ]
            ]
        );
        $needUpdate   = $defaultTheme->get(Theme::THEME_BASE_NAME);
        if (! empty($option)) {
            /**
             * @var Option $first
             */
            $first = $option->getFirst();
            if ($first instanceof Option
                && is_string($first->getOptionValue())
                && $theme->isThemeIsValid($first->getOptionValue())
            ) {
                $needUpdate = false;
                $theme->setActiveTheme($first->getOptionValue());
                $currentTheme = $theme->getActiveTheme();
            } elseif (isset($this['logger'])) {
                /**
                 * @var Service $logger
                 */
                $logger = $this['service'];
                $logger->logException(
                    new \RuntimeException(
                        sprintf(
                            'Theme From database is Invalid, fallback to default %s',
                            $needUpdate
                        )
                    )
                );
            }
        }

        if ($needUpdate) {
            $option = new Option();
            $option->setOptionName('theme:active');
            $option->setOptionValue($needUpdate);
        }
    }

    if ($isBackend) {
        $viewDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR;
    } else {
        if (!$defaultTheme) {
            throw new \RuntimeException(
                'No valid theme exists.',
                E_ERROR
            );
        }

        $viewDir = $currentTheme->get(Theme::THEME_PATH_NAME);
    }

    $view->setViewsDir($viewDir);
    $view->setPartialsDir($viewDir . Theme::BASE_PATH_PARTIAL . DIRECTORY_SEPARATOR);
    $view->registerEngines([
        // use .phtml only
        '.phtml' => View\Engine\Php::class,
        /*'.php' => View\Engine\Php::class,
        '.volt' => function (View $view, $di) {
            $volt = new View\Engine\Volt($view, $di);
            $volt->setOptions([
                'compiledPath' => function ($template) {
                    $hash      = sha1($template);
                    $extension = pathinfo($template, PATHINFO_EXTENSION);
                    $path = Service::TEMP_STORAGE_PATH . '/TemplatesCompiled/';
                    if (!file_exists($path)) {
                        @mkdir($path, 0777, true);
                    }
                    if (!file_exists($path.'.htaccess') && is_dir($path) && is_writeable($path)) {
                        @file_put_contents(
                            $path. '.htaccess',
                            "Deny From All\n"
                        );
                    }
                    $path = realpath($path)?: $path;
                    $path = rtrim($path, '\\/') . DIRECTORY_SEPARATOR;
                    return "{$path}{$hash}.{$extension}.compiled";
                }
            ]);
            $conf = $this['config']['cache']['backend'];
            if ($this['config']['environment'] == 'development'
                || empty($conf['lifetime'])
                || !is_numeric($conf['lifetime'])
                || $conf['lifetime'] < 1
            ) {
                $path = Service::TEMP_STORAGE_PATH . '/TemplatesCompiled/';
                $path = realpath($path) ?: $path;
                $path = rtrim($path, '\\/') . DIRECTORY_SEPARATOR;
                array_map(function ($c) {
                    if (is_file($c)) {
                        unlink($c);
                    }
                }, glob($path . '/*.compiled'));
            }

            return $volt;
        }
        */
    ]);

    /**
     * @var Manager $events
     */
    $events = $this['eventsManager'];
    // set events manager
    $view->setEventsManager($events);
    // attach
    $events->attach(
        'view:beforeRenderView',
        function () {
            /**
             * @var Hook $hook
             * @var View $view
             * @var StringSanity $sanity
             * @var Tag $tag
             * @var Escaper $escaper
             */
            $hook = $this['hook'];
            $view = $this['view'];
            $sanity = $this['sanity'];
            $tag = $this['tag'];
            $escaper = $this['escaper'];
            $bodyClass = [];
            foreach ((array) $hook->apply('body_class', []) as $value) {
                if (!is_string($value) || trim($value) == '') {
                    continue;
                }
                $bodyClass[] = $sanity->filterHtmlClass($value);
            }

            $bodyClass = $sanity->uniqueArray($bodyClass);
            $meta = (array) $hook
                ->apply('meta_list', [
                    [
                        'charset' => $hook->apply('charset', 'utf-8')
                    ]
                ]);
            $blockMeta = [];
            foreach ($sanity->uniqueArray($meta) as $value) {
                if (!is_array($value)) {
                    continue;
                }
                $blockMeta[] = $tag->tagHtml('meta', $value);
            }

            $arrayHtml = (array) $hook->apply('html_attributes', [
                'lang' => $hook->apply('language', 'en')
            ]);

            $htmlAttributes = '';
            foreach ($sanity->uniqueArray($arrayHtml) as $key => $value) {
                if (is_int($key)) {
                    if (!is_string($value) || trim($value) == '') {
                        continue;
                    }
                    $value = trim($value);
                    if (!preg_match('/[^\s\S]/', $value)) {
                        $htmlAttributes .= " {$value}";
                    }
                    continue;
                }
                $htmlAttributes .= "{$tag->renderAttributes('', [$key => $value])}";
            }
            $htmlAttributes = trim($htmlAttributes);
            $htmlAttributes = $htmlAttributes ? " {$htmlAttributes}" : "";
            $bodyAttributes = (array) $hook->apply('body_attributes', ['class' => implode(' ', $bodyClass)]);
            $blockHeaders = $hook->apply('header_content', []);

            $blockHeaders = $sanity->uniqueArray((array) $blockHeaders);
            $blockHeaders = rtrim(implode("\n", $blockHeaders));
            $view->setVars([
                'block_header' => trim(implode("\n    ", (array) $blockHeaders), ' '),
                'block_meta'   => trim(implode("\n    ", (array) $blockMeta), ' '),
                'block_footer' => [],
                'html_attributes' => $htmlAttributes,
                'body_attributes' => $sanity->uniqueArray($bodyAttributes),
            ]);
        }
    );

    return $view;
});
