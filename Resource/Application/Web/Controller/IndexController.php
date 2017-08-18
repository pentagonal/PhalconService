<?php
namespace Pentagonal\Phalcon\Application\Web\Controller;

use Pentagonal\Phalcon\Application\Globals\Library\Hook;
use Pentagonal\Phalcon\Application\Globals\Library\Theme;
use Phalcon\Http\Response;

/**
 * Class IndexController
 * @package Pentagonal\Phalcon\Application\Web\Controller
 */
class IndexController extends ControllerBase
{
    /**
     * @var array
     */
    protected $handler = [
        'archive' => 'archiveHandler',
        'tag'     => 'tagHandler',
        'search'  => 'searchHandler'
    ];

    /**
     * @Route("/")
     *
     * @return \Phalcon\Http\Response
     */
    public function indexAction() : Response
    {
        $this->addBodyClass('home-page');
        return $this->renderTemplateView('home');
    }

    /**
     * @return string
     */
    private function generateRobotsTxt() : string
    {
        /**
         * @var Hook $hook
         * @var Theme $theme
         */
        $hook = $this['hook'];
        $theme = $this['theme'];
        $prefix = self::getManagePrefix();
        $themePath = basename($theme->getThemesDir());
        $disallowedLink = $hook
            ->apply('robots_disallow', [
                "/{$prefix}/",
                "/{$themePath}/"
            ]);
        $link = "";
        foreach ((array) $disallowedLink as $value) {
            if (!is_string($value) || trim($value) == '') {
                continue;
            }
            $value = trim($value);
            $link .= "Disallow: {$value}\r\n";
        }

        $allowedLink = $hook->apply('robots_allow', ['/uploads/images/']);
        $linkAllowed = "";
        foreach ((array) $allowedLink as $value) {
            if (!is_string($value) || trim($value) == '') {
                continue;
            }

            $value = trim($value);
            $linkAllowed .= "Allow: {$value}\r\n";
        }

        $html = "# robots.txt\r\n\r\n";
        $html .= "User-Agent: *\r\n\r\n";
        $html .= "{$link}\r\n";
        $html .= "{$linkAllowed}\r\n";

        return rtrim($html);
    }

    public function robotsAction() : Response
    {
        return $this
            ->response
            ->setContentType('text/plain;charset=utf-8;')
            ->setContent($this->generateRobotsTxt());
    }

    /**
     * @param array ...$params
     *
     * @return Response
     */
    public function slugAction(...$params) : Response
    {
        if (empty($params)) {
            return $this->indexAction();
        }

        if ($params[0] && isset($this->handler[$params[0]])
            && method_exists($this, $this->handler[$params[0]])
        ) {
            $action = $params[0];
            array_shift($params);
            return call_user_func_array([$this, $this->handler[$action]], $params);
        }

        return $this->showNotFound();
    }
}
