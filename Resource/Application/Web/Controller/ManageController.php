<?php
declare(strict_types=1);

namespace Pentagonal\Phalcon\Application\Web\Controller;

use Pentagonal\Phalcon\Application\Globals\Plugin\Auth;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Http\Response;

/**
 * Class ManageController
 * @package Pentagonal\Phalcon\Application\Web\Controller
 *
 * @property-read Auth $auth
 * @property-read Memory $acl
 */
class ManageController extends ControllerBase
{
    /**
     * @param array ...$params
     *
     * @return Response
     */
    public function slugAction(...$params) : Response
    {
        if (! $this->auth->isLogin()) {
            if (count($params) === 1 && in_array($params[0], ['login', 'register'])) {
                $this->addBodyClass(strtolower($params[0]). '-page');
                return call_user_func_array([$this, "{$params[0]}Action"], $params);
            }
            $dashboardUri = $this->getBaseUri(self::getManagePrefix() . self::LOGIN_PATH);
            return $this->response->redirect($dashboardUri);
        }

        if (empty($params)) {
            return $this->showDashboard($params);
        }

        if (isset($params[0]) && method_exists($this, "{$params[0]}Action")) {
            $this->addBodyClass(strtolower($params[0]). '-page');
            return call_user_func_array([$this, "{$params[0]}Action"], $params);
        }

        return $this->showNotFound();
    }

    /**
     * @param array ...$param
     *
     * @return Response
     */
    public function showDashboard(... $param) : Response
    {
        $this->addBodyClass('dashboard-page');

        return $this->showUnauthorized();
    }

    /**
     * @return Response
     */
    public function loginAction()
    {
        $this->setTitle('Login To Member Area');
        return $this->renderManageView('Login');
    }

    public function registerAction()
    {
        $this->setTitle('Create New Account');
        return $this->renderManageView('Register');
    }
}
