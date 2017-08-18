<?php
namespace Pentagonal\Phalcon\Application\Globals\Plugin;

use Pentagonal\Phalcon\Application\Globals\Model\User;
use Phalcon\Acl;
use Phalcon\Config;
use Phalcon\Mvc\User\Plugin;

/**
 * Class Auth
 * @package Pentagonal\Phalcon\Application\Globals\Plugin
 */
class Auth extends Plugin
{
    /**
     * @var User|null
     */
    protected $user;

    /**
     * @var Acl\Role
     */
    protected $acl;

    /**
     * Auth constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * @return null|User
     */
    public function getUser()
    {
        return $this->user;
    }

    private function initialize()
    {
        $auth = $this->session->get('auth');
        if (!$auth || ! $auth instanceof Config) {
            return;
        }

        if (is_string($auth['username']) && is_int($auth['id'])
            && is_int($auth['time']) && $auth['time'] < (time() + 5)
        ) {
            $this->user = User::findFirst(
                [
                    'id = :id:',
                    'bind' => [
                        'id' => $auth['id']
                    ]
                ]
            );
        }
    }

    /**
     * @return Acl\Role
     */
    public function getAcl() : Acl\Role
    {
        if (!$this->acl) {
            $role = 'guest';
            if ($this->user) {
                $role = $this->user->getRole();
            }

            $this->acl = new Acl\Role($role);
        }

        return $this->acl;
    }

    /**
     * @return bool
     */
    public function isLogin() : bool
    {
        return !empty($this->user);
    }
}
