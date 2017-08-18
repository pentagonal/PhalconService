<?php
namespace Pentagonal\Phalcon\Application\Web\Service;

use Pentagonal\Phalcon\Application\Globals\Library\Hook;
use Pentagonal\Phalcon\Application\Globals\Library\RoleList;
use Pentagonal\Phalcon\Application\Globals\Model\User;
use Pentagonal\Phalcon\Service;
use Phalcon\Acl;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Role;

if (!isset($this) || ! $this instanceof Service) {
    return;
}

$this
    ->di
    ->setShared('acl', function () {
        $acl = new Memory();
        $acl->setEventsManager($this['eventsManager']);
        $acl->addRole(new Role(User::GUEST, 'Guest not logged'));
        $acl->addRole(new Role(User::ROLE_UNKNOWN, 'Unknown'));
        $acl->addRole(new Role(User::ROLE_BANNED, 'Banned'), User::ROLE_UNKNOWN);
        $acl->addRole(new Role(User::ROLE_SUBSCRIBER, 'Subscriber'), User::ROLE_UNKNOWN);
        $acl->addRole(new Role(User::ROLE_STANDARD, 'Standard'), User::ROLE_BANNED);
        $acl->addRole(new Role(User::ROLE_VIP, 'VIP'), User::ROLE_STANDARD);
        $acl->addRole(new Role(User::ROLE_CONTRIBUTOR, 'Contributor'), User::ROLE_STANDARD);
        $acl->addRole(new Role(User::ROLE_AUTHOR, 'Author'), User::ROLE_CONTRIBUTOR);
        $acl->addRole(new Role(User::ROLE_ADMIN, 'Administrator'), User::ROLE_AUTHOR);
        $acl->addRole(new Role(User::ROLE_SUPER_ADMIN, 'Super Administrator'), User::ROLE_ADMIN);

        $acl->addResource(new Resource(RoleList::MANAGE_PUBLIC), RoleList::LIST[RoleList::MANAGE_PUBLIC]);
        $acl->addResource(new Resource(RoleList::MANAGE_VIP), RoleList::LIST[RoleList::MANAGE_VIP]);
        $acl->addResource(new Resource(RoleList::MANAGE_POST), RoleList::LIST[RoleList::MANAGE_POST]);
        $acl->addResource(new Resource(RoleList::MANAGE_PAGE), RoleList::LIST[RoleList::MANAGE_PAGE]);
        $acl->addResource(new Resource(RoleList::MANAGE_USER), RoleList::LIST[RoleList::MANAGE_USER]);
        $acl->addResource(new Resource(RoleList::MANAGE_SETTING), RoleList::LIST[RoleList::MANAGE_SETTING]);
        $acl->addResource(new Resource(RoleList::MANAGE_THEME), RoleList::LIST[RoleList::MANAGE_THEME]);
        $acl->addResource(new Resource(RoleList::MANAGE_MEDIA), RoleList::LIST[RoleList::MANAGE_MEDIA]);
        $bannedPending = function () {
            /**
             * @var Hook $Hook
             * @var Memory $acl
             */
            $Hook = $this['hook'];
            $acl = $this['acl'];
            return $Hook->apply(
                'role_access',
                Acl::ALLOW,
                $acl,
                $acl->getActiveRole(),
                $acl->getActiveResource(),
                $acl->getActiveResource()
            ) ? Acl::ALLOW : Acl::DENY;
        };

    foreach (RoleList::getDefault() as $role => $authorList) {
        foreach ($authorList as $access => $accessList) {
            foreach ($accessList as $accessName) {
                $acl->allow($role, $access, $accessName, $bannedPending);
            }
        }
    }

        // Default action is deny access
        $acl->setDefaultAction(Acl::DENY);
        return $acl;
    });
