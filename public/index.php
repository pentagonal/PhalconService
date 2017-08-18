<?php
declare(strict_types=1);

namespace {

    use Pentagonal\Phalcon\Service;
    use Phalcon\Config;
    use Phalcon\Http\Response;
    use Phalcon\Mvc\Application;

    if (!extension_loaded('phalcon')) {
        header('Content-Type: text/html; charset=utf-8', true, 500);
        echo "Phalcon extension is not installed.";
        exit(1);
    }

    /**
     * @var Application $app
     * @var Config|Config[]|array $config
     */
    require_once __DIR__ . '/../Resource/Service.php';
    /**
     * @var Application $app
     */
    $app =  Service::useConfig(require __DIR__ . '/../Web.Config.php');

    /**
     * @var Response $response
     */
    $response = $app->handle();
    if (!$response->isSent()) {
        $response->send();
    }

    return $response;
}
