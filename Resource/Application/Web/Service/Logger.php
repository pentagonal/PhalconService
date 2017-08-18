<?php
namespace Pentagonal\Phalcon\Application\Web\Service;

use Pentagonal\Phalcon\Application\Web\Controller\ControllerBase;
use Pentagonal\Phalcon\Service;
use Phalcon\Http\Response;
use Phalcon\Logger;
use Phalcon\Mvc\View;
use Phalcon\Tag;

if (!isset($this) || !$this instanceof Service) {
    return;
}

return $this
    ->di
    ->setShared('logger', function () {
        $stream = new Logger\Adapter\File(__DIR__ . '/../../../../Storage/Temporary/Log/web.log');
        $logLevel = $this['config']['logLevel']?: Logger::ERROR;
        $stream->setLogLevel($logLevel);
        if ($this['config']['environment'] != 'development') {
            $this['errorHandler'] = function () {
                return function ($e) {
                    $controller = new ControllerBase();
                    $controller->removeHtmlAttribute('⚡');
                    $controller->addBodyClass('error-500');
                    /**
                     * @var View $view
                     * @var Response $response
                     */
                    $view = $this['view'];
                    $response = $this['response'];
                    $view = $view->render('Default', '500', ['exception' => $e]);
                    $view = $view->finish();
                    Tag::setTitle('Internal Server Error');
                    $response->setContent($view->getContent());
                    $response->setStatusCode(500);
                    $response->send();
                    exit(255);
                };
            };
        }

        return $stream;
    });
