<?php
namespace Pentagonal\Phalcon\Application\Web\Controller;

use Pentagonal\Phalcon\Application\Globals\Library\Controller;
use Pentagonal\Phalcon\Application\Globals\Library\Hook;
use Pentagonal\Phalcon\Application\Globals\Library\StringSanity;
use Pentagonal\Phalcon\Application\Globals\Library\Theme;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Http\Response;

/**
 * Class ControllerBase
 * @package Pentagonal\Phalcon\Application\Web\Controller
 */
class ControllerBase extends Controller
{
    const MANAGE_PREFIX = 'manage';
    const LOGIN_PATH    = '/login';

    /**
     * @var string
     */
    protected static $manageUri = self::MANAGE_PREFIX;

    /**
     * @var bool
     */
    private static $hasSetManage = false;

    /**
     * @return string
     */
    final public static function getManagePrefix() : string
    {
        if (self::$hasSetManage) {
            return self::$manageUri;
        }

        self::$hasSetManage = true;
        /**
         * @var Config $config
         */
        $config = Di::getDefault()['config'];
        $manage = $config->get('managePage', self::MANAGE_PREFIX);
        $manage = ! is_string($manage) || trim($manage) == ''
            ? self::MANAGE_PREFIX
            : $manage;
        $manage = preg_replace(
            [
                '#[^a-z0-9\_\-/]#i',
                '#(\\\|/)+#',
            ],
            [
                '-',
                '/'
            ],
            $manage
        );
        $manage = trim($manage, '/');
        return self::$manageUri = $manage;
    }

    /**
     * Init when Controller on construct
     */
    public function onConstruct()
    {
        $this->addMeta([
            [
                'name' => 'viewport',
                'content' => 'width=device-width, '
                             . 'initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0'
            ],
            [
                'http-equiv' => 'X-UA-Compatible',
                'content' => 'ie=edge'
            ],
        ]);

        // $this->addHtmlAttribute('⚡', true);
        $this->addHtmlAttribute('class', 'no-js');
        if ($this->auth->isLogin()) {
            $this->addBodyClasses(['logged', 'user-'.$this->auth->getUser()->getId()]);
        } else {
            $this->addBodyClass('guest');
        }
    }

    /**
     * @param array ...$params
     *
     * @return Response
     */
    public function renderView(...$params) : Response
    {
        return call_user_func_array([$this->di['service'], 'renderViewResponse'], $params);
    }

    /**
     * @param array ...$params
     *
     * @return Response
     */
    public function renderTemplateView(...$params) : Response
    {
        array_unshift($params, Theme::BASE_PATH_TEMPLATE);
        return call_user_func_array([$this, 'renderView'], $params);
    }

    /**
     * @param array ...$params
     *
     * @return Response
     */
    public function renderManageView(...$params) : Response
    {
        array_unshift($params, 'manage');
        return call_user_func_array([$this, 'renderView'], $params);
    }

    /**
     * @param string $title
     *
     * @return Controller
     */
    public function setTitle(string $title) : Controller
    {
        $this->tag->setTitle($title);
        return $this;
    }

    /**
     * @return \Phalcon\Http\Response
     */
    public function showNotFound() : Response
    {
        // $this->removeHtmlAttribute('⚡');
        /**
         * @var Hook $hook
         */
        $hook = $this['hook'];
        $title = $hook->apply('title_404', '404 Page Not Found');
        if (!is_string($title)) {
            $title = '404 Page Not Found';
        }
        $this->setTitle($title);
        $this->addBodyClass('not-found-404');
        return $this->renderTemplateView('404')->setStatusCode(404);
    }

    /**
     * @return \Phalcon\Http\Response
     */
    public function showUnauthorized() : Response
    {
        // $this->removeHtmlAttribute('⚡');
        /**
         * @var Hook $hook
         */
        $hook = $this['hook'];
        $title = $hook->apply('title_401', '401 Unauthorized');
        if (!is_string($title)) {
            $title = '401 Unauthorized';
        }
        $this->setTitle('401 Unauthorized');
        $this->addBodyClass('unauthorized-401');
        return $this->renderTemplateView('401')->setStatusCode(401);
    }

    /**
     * @param array $metas
     *
     * @return Controller
     */
    public function addMeta(array $metas) : Controller
    {
        $realMeta = [];
        foreach ($metas as $meta) {
            if (is_array($meta)) {
                $stop = false;
                foreach ($meta as $k => $m) {
                    if (is_array($m) || is_int($k)) {
                        $stop = true;
                        break;
                    }
                }

                if (!$stop) {
                    $realMeta[] = $meta;
                }

                continue;
            }
        }
        if (empty($realMeta)) {
            $realMeta = $metas;
        }

        $this
            ->di['hook']
            ->add('meta_list', function (array $meta) use ($realMeta) {
                $meta = array_merge($meta, $realMeta);
                return $meta;
            });

        return $this;
    }

    /**
     * @param mixed $param
     *
     * @return string
     */
    private function sanityTrim($param) : string
    {
        if (!is_string($param) || trim($param) == '') {
            return '';
        }

        return trim($param);
    }

    /**
     * @param array ...$param
     *
     * @return Controller
     */
    public function removeHtmlAttribute(...$param) : Controller
    {
        if (empty($param)) {
            return $this;
        }
        $params = [];
        foreach ($param as $key => $value) {
            if (is_string($value) || trim($value) != '') {
                $params[] = $value;
            }
        }

        if (!empty($param)) {
            $this
                ->di['hook']
                ->add('html_attributes', function (array $attr) use ($params) {
                    foreach ($attr as $key => $param) {
                        if (is_int($key) && in_array($param, $params)
                            || is_string($key) && in_array($key, $params)
                        ) {
                            unset($attr[$key]);
                        }
                    }

                    return $attr;
                });
        }

        return $this;
    }

    /**
     * @param array ...$params
     *
     * @return Controller
     */
    public function addHtmlAttribute(...$params) : Controller
    {
        if (count($params) === 0) {
            return $this;
        }
        $prepend = isset($params[2]) ? (bool) $params[2] : false;
        if (count($params) > 1) {
            if (count($params) === 2 && is_bool($params[1])) {
                $prepend = (bool) $params[1];
            } elseif (count($params) > 2 && (is_null($params[1]) || is_bool($params[1]))) {
                $prepend = (bool) $params[2];
                $params = [$params[0]];
            } else {
                $params = [
                    $params[0] => (is_bool($params[1]) ? ($params[1] ? 'true' : 'false'): $params[1])
                ];
            }
        }

        return $this->addHtmlAttributes($params, $prepend);
    }

    /**
     * @param array $htmlAttr
     * @param bool $prepend
     *
     * @return Controller
     */
    public function addHtmlAttributes(array $htmlAttr, $prepend = false) : Controller
    {
        $this
            ->di['hook']
            ->add('html_attributes', function (array $attr) use ($htmlAttr, $prepend) {
                $attr = $prepend ? array_merge($htmlAttr, $attr) : array_merge($attr, $htmlAttr);
                return $attr;
            });

        return $this;
    }

    /**
     * @param array ...$param
     *
     * @return Controller
     */
    public function removeClass(...$param) : Controller
    {
        if (empty($param)) {
            return $this;
        }
        $params = [];
        foreach ($param as $key => $value) {
            if (is_string($value) || trim($value) != '') {
                $params[] = $value;
            }
        }

        if (!empty($param)) {
            $this
                ->di['hook']
                ->add('html_attributes', function (array $attr) use ($params) {
                    foreach ($attr as $key => $param) {
                        if (in_array($param, $params)) {
                            unset($attr[$key]);
                        }
                    }

                    return array_values($attr);
                });
        }
        return $this;
    }

    /**
     * @param string $param
     *
     * @return Controller
     */
    public function addBodyClass(string $param) : Controller
    {
        return $this->addBodyClasses([$param]);
    }

    /**
     * @param array $classes
     *
     * @return Controller
     */
    public function addBodyClasses(array $classes) : Controller
    {
        $this
            ->di['hook']
            ->add('body_class', function ($class) use ($classes) {
                $class = array_merge($class, array_map([$this, 'sanityTrim'], $classes));

                /**
                 * @var StringSanity[] $this
                 */
                return $this->di['sanity']->uniqueArray($class);
            });

        return $this;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getAssetUri(string $path = '') : string
    {
        $path = ltrim($path, '/');
        $assetPath = "assets/{$path}";
        return $this->getBaseUri($assetPath);
    }
    /**
     * @param string $path
     *
     * @return string
     */
    public function getThemeUri(string $path = '') : string
    {
        /**
         * @var Theme $theme
         */
        $theme = $this['theme'];
        $themePath = basename($theme->getThemesDir()) .'/' . $theme->getActiveThemeBasePath();
        $path = ltrim($path, '/');
        $assetPath = "{$themePath}/{$path}";
        return $this->getBaseUri($assetPath);
    }
}
