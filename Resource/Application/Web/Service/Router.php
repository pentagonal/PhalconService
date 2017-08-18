<?php
namespace Pentagonal\Phalcon\Application\Web\Service;

use Pentagonal\Phalcon\Application\Globals\Library\Theme;
use Pentagonal\Phalcon\Service;
use Phalcon\Dispatcher;
use Phalcon\Events\Event;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url;

if (!isset($this) || !$this instanceof Service) {
    return;
}

return $this
    ->di
    ->setShared('router', function () {
        $router = new Router(false);
        $router->setEventsManager($this['eventsManager']);
        $router->setDefaultNamespace('Pentagonal\Phalcon\Application\Web\Controller');
        $router->setDefaultController('Default');
        $router->setDefaultAction('index');
        $router->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI);
        $router
            ->getEventsManager()
            ->attach('router:afterCheckRoutes', function (Event $event, Router $router) {
                /**
                 * @var Url $url
                 * @var Theme $theme
                 */
                $url = $this['url'];
                $theme = $this['theme'];
                if ($router->wasMatched()) {
                    $route = $router->getMatchedRoute();
                    $host = parse_url($url->getBaseUri(), PHP_URL_HOST);
                    $route->setHostname($host);
                }
                if (file_exists($theme->getActiveThemeDir() . 'init.php')) {
                    /** @noinspection PhpIncludeInspection */
                    require_once $theme->getActiveThemeDir() . 'init.php';
                }
            });
        return $router;
    });
