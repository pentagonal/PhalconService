<?php
declare(strict_types=1);

namespace Pentagonal\Phalcon;

use Pentagonal\Phalcon\Application\Globals\Library\Hook;
use Pentagonal\Phalcon\Application\Globals\Library\StringSanity;
use Phalcon\Acl\Adapter\Memory as MemoryAcl;
use Phalcon\Cache\Backend\Factory as BackendFactory;
use Phalcon\Cache\Backend\Memory as MemoryBackendCache;
use Phalcon\Cache\BackendInterface;
use Phalcon\Cache\Frontend\Factory as FrontCacheFactory;
use Phalcon\Cache\FrontendInterface;
use Phalcon\Config;
use Phalcon\Db\AdapterInterface as DbAdapterInterface;
use Phalcon\Db\Adapter\Pdo\Factory as DbFactory;
use Phalcon\Di;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Manager as EventManager;
use Phalcon\Flash\Direct as FlashDirect;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Http\Response;
use Phalcon\Loader;
use Phalcon\Logger\AdapterInterface as LoggerAdapterInterface;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\View;
use Phalcon\Session\AdapterInterface as SessionAdapterInterface;
use Phalcon\Session\Bag as SessionBag;
use Phalcon\Session\Factory as SessionFactory;
use Phalcon\Tag;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * Class Service
 * @package Pentagonal\Phalcon
 *
 * @property-read Di $di
 * @property-read DbAdapterInterface $db
 * @property-read Application $app
 * @property-read MemoryAcl $acl
 * @property-read EventManager $eventsManager
 * @property-read Service $service
 * @property-read View $view
 * @property-read Hook $hook
 * @property-read Tag $tag
 * @property-read StringSanity $sanity
 * @property-read SessionBag $sessionBag
 * @property-read SessionAdapterInterface $session
 * @property-read FlashDirect $flash
 * @property-read FlashSession $flashSession
 * @property-read BackendInterface $cache
 * @property-read FrontendInterface $frontCache
 */
class Service implements \ArrayAccess
{
    /**
     * @var string
     */
    public $appPrivate;

    const STORAGE_PATH = __DIR__ . '/../Storage/';
    const UPLOAD_STORAGE_PATH = self::STORAGE_PATH . 'Files/';
    const TEMP_STORAGE_PATH = self::STORAGE_PATH . 'Temporary/';
    const CACHE_STORAGE_PATH = self::TEMP_STORAGE_PATH . 'Cache/';
    const LOG_STORAGE_PATH = self::TEMP_STORAGE_PATH .'Log/';
    const SESSION_STORAGE_PATH = self::TEMP_STORAGE_PATH .'Session/';

    /**
     * @var bool
     */
    protected $allowIncludeDirs = false;

    /**
     * Service constructor.
     *
     * @param array $config
     */
    private function __construct(array $config)
    {
        /**
         * @var Application $app
         */
        $this->appPrivate = new class(new FactoryDefault()) extends Application implements \ArrayAccess
        {
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
                $this->di->set($offset, $value);
            }

            /**
             * @param mixed $name
             *
             * @return bool
             */
            public function __isset($name)
            {
                return $this->offsetExists($name);
            }
        };

        $this->registerDefaultServices();
        $this->di->setShared('config', new Config($config));
    }

    public function offsetUnset($offset)
    {
        unset($this->appPrivate[$offset]);
    }

    public function offsetExists($offset)
    {
        return isset($this->appPrivate[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->appPrivate[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->appPrivate[$offset] = $value;
    }

    /**
     * @return Service
     */
    public function allowInstanceIncludeDirs() : Service
    {
        $this->allowIncludeDirs = true;
        return $this;
    }

    /**
     * @return Service
     */
    public function disAllowInstanceIncludeDirs() : Service
    {
        $this->allowIncludeDirs = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowInstanceIncludeDirs() : bool
    {
        return (bool) $this->allowIncludeDirs;
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->appPrivate, $name], $arguments);
    }

    /**
     * @param $name
     */
    public function __get($name)
    {
        return $this->appPrivate->{$name};
    }

    /**
     * @param array $config
     *
     * @return Service
     */
    public static function useConfig(array $config) : Service
    {
        $object = new static($config);

        return $object->process();
    }

    public function handle()
    {
        if (!isset($this['view'])) {
            $this->di->setShared('view', function () {
                return new View();
            });
        }

        return $this->appPrivate->handle();
    }

    /**
     * @param array ...$param
     *
     * @return Response
     */
    public function renderViewResponse(... $param) : Response
    {
        /**
         * @var View $view
         */
        $view = call_user_func_array([$this['view'], 'render'], $param);
        $view->finish();
        return $this->responseSetContent($view->getContent());
    }

    /**
     * @param string $content
     *
     * @return Response
     */
    public function responseSetContent(string $content) : Response
    {
        /**
         * @var Response[] $this
         */
        $response = $this['response']->setContent($content);
        return $response;
    }

    /**
     * @return Service
     */
    private function process(): Service
    {
        // auto load composer
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            /** @noinspection PhpIncludeInspection */
            $app['autoload'] = require __DIR__ . '/../vendor/autoload.php';
        }

        $this->registerErrorHandler();
        $this->registerServices();
        return $this;
    }

    private function registerDefaultServices()
    {
        // set default
        $this->di->setDefault($this->appPrivate->di);
        $this->di->setShared('service', $this);
        $this->di->setShared('app', $this->appPrivate);
        $this->di->setShared('loader', Loader::class);

        /**
         * @var Loader $loader
         */
        $loader = $this['loader'];
        $loader->registerNamespaces(['Pentagonal\\Phalcon\\Application\\Globals' => __DIR__ . '/Global',]);
        $loader->register();
        $this->di->setShared('acl', MemoryAcl::class);
        $this->di->setShared('eventsManager', EventManager::class);
        $this->di->setShared('tag', Tag::class);
        $this->di->setShared('hook', Hook::class);
        $this->di->setShared('sanity', StringSanity::class);
        $this->di->set('view', View::class);
        $this->di->set('viewCache', MemoryBackendCache::class);
        $this->di->setShared('db', function () {
            /**
             * @var Config $config
             */
            $config = $this['config'];
            $dbConfig = $config->get('database', new Config());
            /**
             * @var DbAdapterInterface $db
             */
            $db = DbFactory::load($dbConfig);
            return $db;
        });

        $this->di->setShared('frontCache', function () {
            /**
             * @var Config $config
             * @var Config $cacheConfig
             */
            $config = $this['config'];
            $cacheConfig = $config->get('cache', new Config);
            $frontCache  = $cacheConfig instanceof Config
                ? $cacheConfig->get('frontend')
                : new Config([
                    "lifetime" => 3600,
                    "adapter"  => "data",
                ]);
            if ($config['environment'] == 'development') {
                $frontCache['lifetime'] = 0;
            }

            !isset($frontCache['adapter']) && $frontCache['adapter'] = 'data';
            return FrontCacheFactory::load($frontCache);
        });

        $this->di->setShared('cache', function () {
            /**
             * @var Config|Config[] $config
             * @var Config $cacheConfig
             */
            $config = $this['config'];
            $cacheConfig = $config->get('cache', new Config);
            $backendCache  = $cacheConfig instanceof Config
                ? $cacheConfig->get('backend')
                : new Config([
                    "lifetime" => 3600,
                    "adapter"  => "file",
                    "cacheDir" => self::CACHE_STORAGE_PATH
                ]);
            if (!isset($backendCache['adapter']) || !is_string($backendCache['adapter'])) {
                $backendCache['adapter'] = 'file';
            }
            $backendCache['adapter'] = strtolower($backendCache['adapter']);
            if ($backendCache['adapter'] == 'file') {
                $backendCache['cacheDir'] = self::CACHE_STORAGE_PATH;
            }
            if ($config['environment'] == 'development') {
                $backendCache['compileAlways'] = true;
                $backendCache['lifetime'] = 0;
            }
            if ($backendCache['adapter'] == 'file') {
                if ($this['config']['environment'] == 'development'
                    || ! empty($backendCache['compileAlways'])
                    || isset($backendCache['lifetime'])
                       && (
                           is_numeric($backendCache['lifetime'])
                           || $backendCache['lifetime'] < 1
                       )
                ) {
                    $path = $backendCache['cacheDir'];
                    $path = realpath($path) ?: $path;
                    $path = rtrim($path, '\\/') . DIRECTORY_SEPARATOR;
                    array_map(function ($c) {
                        if (is_file($c)) {
                            unlink($c);
                        }
                    }, glob($path . '/*.compiled'));
                }
            }

            // always stat
            $backendCache['stat'] = true;
            $backendCache['key'] = 'backendCache';
            $backendCache['frontend'] = $this['frontCache'];
            $config['cache']['backend'] = $backendCache;
            $cache = BackendFactory::load($backendCache);
            return $cache;
        });

        $this->di->set('session', function () {
            /**
             * @var Config $config
             */
            $config = $this['config'];
            $sessionConfig = $config->get('session', new Config());
            if (!isset($session['adapter']) || $sessionConfig['adapter'] == 'files') {
                $sessionConfig['path'] = self::SESSION_STORAGE_PATH;
            }

            if (!isset($sessionConfig['name']) || !is_string($sessionConfig['name'])) {
                $sessionConfig['name'] = session_name();
            }
            if ($sessionConfig['name']) {
                session_name($sessionConfig['name']);
            }

            // if ($sessionConfig['adapter'] == 'files') {
            // save session into storage
            session_save_path($sessionConfig['path']);
            //}

            $session = SessionFactory::load($sessionConfig);
            ! $session->isStarted() && $session->start();
            return $session;
        });

        $this->di->set('sessionBag', function () {
            /**
             * @var SessionAdapterInterface $session
             */
            $session = $this['session'];
            $session->getName();
            $name = $session->getName() ?: (session_name()?: __DIR__);
            $bag = new SessionBag($name);
            return $bag;
        });

        $this->di->setShared('flash', function () {
            $flash = new FlashDirect();
            return $flash;
        });

        $this->di->setShared('flashSession', function () {
            $flashSession = new FlashSession();
            return $flashSession;
        });

        $this->di->setShared('url', function () {
            /**
             * @var Config $config
             */
            $config = $this['config'];
            $baseUrl = $config->get('baseUrl');
            if (!$baseUrl) {
                $path = substr(
                    $_SERVER['SCRIPT_NAME'],
                    0,
                    strpos(
                        $_SERVER['SCRIPT_NAME'],
                        basename($_SERVER['SCRIPT_FILENAME'])
                    )
                );
                $baseUrl = trim($path, '/') . '/';
                $host = '';
                if (isset($_SERVER['HTTP_HOST'])) {
                    $host = $_SERVER['HTTP_HOST'];
                } elseif (isset($_SERVER['SERVER_NAME'])) {
                    $host = $_SERVER['SERVER_NAME'];
                } elseif (isset($_SERVER['SERVER_ADDR'])) {
                    // this is IPV 6 ipv 6 accessed by http(s)://[11:22:33:44]/
                    if (strpos($_SERVER['SERVER_ADDR'], ':') !== false) {
                        $host = "[{$_SERVER['SERVER_ADDR']}]";
                    } else {
                        $host = $_SERVER['SERVER_ADDR'];
                    }
                }
                if ($host && isset($_SERVER['SERVER_PORT'])) {
                    $host .= $_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443
                        ? ':' . $_SERVER['SERVER_PORT']
                        : '';
                }
                if ($host != '') {
                    $protocol = 'http';
                    if (! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off'
                        || isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                           && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'
                        || ! empty($_SERVER['HTTP_FRONT_END_HTTPS'])
                           && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off'
                    ) {
                        $protocol .= 's';
                    }
                    $baseUrl = ltrim($baseUrl, '/');
                    $baseUrl = "{$protocol}://{$host}/{$baseUrl}";
                }
            }

            $url = new Url();
            $url->setBaseUri($baseUrl);
            $url->setStaticBaseUri($baseUrl);
            return $url;
        });
    }

    private function registerServices()
    {
        /**
         * @var Loader $loader
         */
        $loader = $this['loader'];

        /**
         * @var Config
         */
        $configLoader = $this['config']->get('nameSpace', new Config());
        $includePaths = $this['config']->get('includePath', null);
        $includePaths = $includePaths instanceof Config
            ? $includePaths->toArray()
            : [];
        if ($configLoader instanceof Config) {
            $loader->registerNamespaces($configLoader->toArray(), true);
            $loader->register();
        }

        if (!is_array($includePaths)) {
            $includePaths = [$includePaths];
        }

        foreach ($includePaths as $include) {
            if (!is_string($include)) {
                continue;
            }
            if (is_file($include)) {
                /** @noinspection PhpIncludeInspection */
                require $include;
                continue;
            }

            // check include file on directory
            if ( !$this->isAllowInstanceIncludeDirs() || !is_dir($include)) {
                continue;
            }

            // require service
            foreach (new \DirectoryIterator($include) as $dir) {
                if ($dir->isFile() && $dir->getExtension() == 'php') {
                    (function ($path) {
                        /** @noinspection PhpIncludeInspection */
                        require $path;
                    })->call($this, $dir->getRealPath());
                }
            }
        }
    }

    /**
     * @param \Throwable $e
     */
    public function logException(\Throwable $e)
    {
        $logger = isset($this['logger']) ? $this['logger'] : null;
        if ($logger instanceof LoggerAdapterInterface) {
            $logger
                ->error(
                    sprintf(
                        '[message: %1$s] [file: %2$s] [line: %3$s] [code: %4$s] [class: %5$s]',
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine(),
                        $e->getCode(),
                        get_class($e)
                    ),
                    ['exception' => $e]
                );
        }
    }

    /**
     * Register Error Handler
     */
    private function registerErrorHandler()
    {
        // handle error
        set_error_handler(function ($code, $message, $file, $line) {
            if (error_reporting() & $code) {
                throw new \ErrorException($message, $code, 1, $file, $line);
            }
        });

        $obj = $this;
        // handle exception
        set_exception_handler(function (\Throwable $e) use ($obj) {
            $obj->logException($e);
            $isDevelopment = $obj['config']['environment'] == 'development';
            if ($isDevelopment && class_exists(PrettyPageHandler::class)) {
                $whoops = new Run();
                $whoops->pushHandler(new PrettyPageHandler);
                $whoops->handleException($e);
                exit(255);
            }

            $handler = null;
            if (isset($obj->di['errorHandler'])) {
                if (is_callable($obj->di['errorHandler'])) {
                    call_user_func_array($obj->di['errorHandler'], [$e, $obj]);
                } elseif (is_object($obj->di['errorHandler'])
                          && method_exists($obj->di['errorHandler'], '__invoke')
                ) {
                    $handler = function ($e) {
                        return call_user_func_array([$this->di['errorHandler'], '__invoke'], [$e, $this]);
                    };
                } elseif (is_string($obj->di['errorHandler']) && class_exists($obj->di['errorHandler'])) {
                    $reflection = new \ReflectionClass($obj->di['errorHandler']);
                    if ($reflection->isInstantiable() && $reflection->isUserDefined()) {
                        $handler = function ($e) use ($reflection) {
                            return $reflection->newInstanceArgs([$e, $this]);
                        };
                    }
                }
            }

            $obj->handleExceptionTrace($handler, $e);
            exit(255);
        });
    }

    /**
     * @param $closure
     * @param \Throwable $exception
     *
     * @return mixed
     */
    private function handleExceptionTrace($closure, \Throwable $exception)
    {
        if (!headers_sent()) {
            header('Content-Type: text/html;charset=utf-8', true, 500);
        }
        if ($closure instanceof \Closure) {
            $closure = $closure->bindTo($this);
            return $closure($exception);
        }

        $template = sprintf(
            '<!DOCTYPE html><html><head><meta charset="utf-8">'
                . '<title>%1$s</title>%2$s</head><body>%3$s</body></html>',
            'Internal Server Error',
            sprintf(
                '<style type="text/css">%s</style>',
                '*,*:after,*:before{box-sizing:border-box;}'
                . 'body{background:#f1f1f1;font-size:14px;color:#333;font-family:helvetica, arial, sans-serif;}'
                . 'body{line-height: normal}'
                . 'h1{font-size:14em;margin:.6em 0 .05em;text-align:center;letter-spacing:1px;}'
                . 'h3{font-size:1.5em;margin: .1em 0 .1em;text-align:center;letter-spacing:1px;}'
                . '.wrap {margin: 20vh auto 1em;max-width: 900px;width:100%%;padding:20px;}'
                . '.wrap {background: #fff;border:1px solid #ddd;border-radius:4px;}'
                . '.wrap * {max-width: 100%%;font-size:13px;}'
                . 'p {word-break:break-all;break-word:word-break;}'
                . 'span {width: 100px;display:inline-block;font-weight:bold;}'
                . '.wrap code {font-family:mono, monospace;font-size:12px;padding:2px 5px;}'
                . '.wrap code {background:#eee;border-radius:3px;border:1px solid #ddd}'
                . '.wrap pre {background: #f0f0f0;padding:20px;border:1px solid #ddd;position:relative;}'
                . '.wrap pre {border-radius:3px;overflow:auto;max-height: 200px;}'
                . '.wrap pre {font-family:mono, monospace;font-size:12px;margin-top:2em;}'
                . '.wrap pre:before {border-left:4px solid #ee09a1;content:"";padding:0px;left:0px;}'
                . '.wrap pre:before {position:absolute;top:0;bottom:0;}'
            ),
            '%s'
        );

        if ($this['config']['environment'] === 'development') {
            $html = '<div class="wrap">';
            $html .= "<p><span>Code</span> : <code>{$exception->getCode()}</code></p>";
            $html .= "<p><span>Message</span> : {$exception->getMessage()}</p>";
            $html .= "<p><span>File</span> : {$exception->getFile()}</p>";
            $html .= "<p><span>Line</span> : <code>{$exception->getLine()}</code>";
            $html .= "<p><span>Trace</span><br/><pre>{$exception->getTraceAsString()}</pre></p>";
            $html .= '</div>';
            printf($template, $html);
            return $this;
        }

        printf($template, '<h1>500</h1><h3>Internal Server Error</h3>');
        return $this;
    }
}
