<?php
namespace Pentagonal\Phalcon\Application\Globals\Library;

/**
 * Class RoleList
 */
class RoleList
{
    const ROLE_SUPER_ADMIN    = 'admin:super';
    const ROLE_ADMIN          = 'admin:admin';
    const ROLE_AUTHOR         = 'member:author';
    const ROLE_CONTRIBUTOR    = 'member:contributor';
    const ROLE_STANDARD       = 'member:standard';
    const ROLE_VIP            = 'member:vip';
    const ROLE_UNKNOWN        = 'unknown';
    const GUEST               = 'guest';
    const ROLE_SUBSCRIBER     = 'subscriber';
    const ROLE_BANNED         = 'banned';

    // manage
    const MANAGE_PUBLIC = 'MANAGE_PUBLIC';
    const MANAGE_VIP = 'MANAGE_VIP';
    const MANAGE_POST = 'MANAGE_POST';
    const MANAGE_PAGE = 'MANAGE_PAGE';
    const MANAGE_USER = 'MANAGE_USER';
    const MANAGE_SETTING = 'MANAGE_SETTING';
    const MANAGE_THEME = 'MANAGE_THEME';
    const MANAGE_MEDIA = 'MANAGE_MEDIA';

    const ACCESS_CREATE = 'create';
    const ACCESS_READ = 'read';
    const ACCESS_EDIT = 'edit';
    const ACCESS_DELETE = 'delete';
    const ACCESS_DELETE_PUBLISH = 'delete_publish';
    const ACCESS_REVIEW = 'review';
    const ACCESS_PUBLISH = 'publish';
    const ACCESS_SWITCH = 'switch';
    const ACCESS_UPLOAD = 'upload';
    const ACCESS_LIST = 'list';

    const ACCESS_READ_SELF = 'self::read';
    const ACCESS_EDIT_SELF = 'self::edit';
    const ACCESS_EDIT_DRAFT_SELF = 'self::edit_draft';
    const ACCESS_DELETE_SELF = 'self::delete';
    const ACCESS_DELETE_PUBLISH_SELF = 'self::delete_publish';
    const ACCESS_PUBLISH_SELF  = 'self::publish';
    const ACCESS_READ_PUBLISHED = 'read:published';
    const ACCESS_READ_PENDING = 'read:pending';
    const ACCESS_READ_DELETED = 'read:deleted';

    const CHANGE_ROLE = 'change_role';
    const CHANGE_STATUS = 'change_status';
    const EDIT_ADMIN = 'edit_admin';
    const CHANGE_ROLE_ADMIN = 'change_role_admin';
    const CREATE_STATUS_ADMIN  = 'create_status_admin';
    const DELETE_ADMIN = 'delete_admin';
    const EDIT_ADMIN_SUPER = 'edit_admin_super';
    const CHANGE_ROLE_ADMIN_SUPER = 'change_role_admin_super';
    const CREATE_STATUS_ADMIN_SUPER = 'create_status_admin_super';
    const DELETE_ADMIN_SUPER = 'delete_admin_super';

    const DEFAULT_EDITOR = [
        self::ACCESS_CREATE,
        self::ACCESS_READ,
        self::ACCESS_READ_PUBLISHED,
        self::ACCESS_READ_PENDING,
        self::ACCESS_READ_DELETED,
        self::ACCESS_EDIT,
        self::ACCESS_DELETE,
        self::ACCESS_REVIEW,
        self::ACCESS_PUBLISH,
        self::ACCESS_DELETE_PUBLISH,
        // self
        self::ACCESS_DELETE_SELF,
        self::ACCESS_DELETE_PUBLISH_SELF,
        self::ACCESS_EDIT_SELF,
        self::ACCESS_EDIT_DRAFT_SELF,
        self::ACCESS_READ_SELF,
        self::ACCESS_PUBLISH_SELF,
    ];

    /**
     * Array List
     */
    const LIST =  [
        self::MANAGE_PUBLIC => [self::ACCESS_READ],
        self::MANAGE_VIP => [self::ACCESS_READ],
        self::MANAGE_POST => self::DEFAULT_EDITOR,
        self::MANAGE_PAGE => self::DEFAULT_EDITOR,
        self::MANAGE_USER => [
            self::ACCESS_CREATE,
            self::ACCESS_DELETE,
            self::ACCESS_EDIT,
            self::ACCESS_READ,
            self::CHANGE_ROLE,
            self::CHANGE_STATUS,
            self::EDIT_ADMIN,
            self::CHANGE_ROLE_ADMIN,
            self::CREATE_STATUS_ADMIN,
            self::DELETE_ADMIN,
            self::EDIT_ADMIN_SUPER,
            self::CHANGE_ROLE_ADMIN_SUPER,
            self::CREATE_STATUS_ADMIN_SUPER,
            self::DELETE_ADMIN_SUPER,
            self::ACCESS_EDIT_SELF,
            self::ACCESS_READ_SELF,
        ],
        self::MANAGE_SETTING => [
            self::ACCESS_READ,
            self::ACCESS_EDIT
        ],
        self::MANAGE_THEME => [
            self::ACCESS_READ,
            self::ACCESS_UPLOAD,
            self::ACCESS_EDIT,
            self::ACCESS_DELETE,
            self::ACCESS_SWITCH,
        ],
        self::MANAGE_MEDIA => [
            self::ACCESS_READ,
            self::ACCESS_UPLOAD,
            self::ACCESS_EDIT,
            self::ACCESS_DELETE,
            self::ACCESS_LIST,
            self::ACCESS_EDIT_SELF,
            self::ACCESS_DELETE_SELF
        ]
    ];

    public static function getDefault() : array
    {
        // contrib
        $managePostsNew = RoleList::LIST[self::MANAGE_POST];
        $managePagesNew = RoleList::LIST[self::MANAGE_PAGE];
        $manageUsersNew = RoleList::LIST[self::MANAGE_USER];
        $manageMediasNew = RoleList::LIST[self::MANAGE_MEDIA];
        unset(
            $managePostsNew[self::ACCESS_EDIT],
            $managePostsNew[self::ACCESS_DELETE],
            $managePostsNew[self::ACCESS_REVIEW],
            // pages
            $managePagesNew[self::ACCESS_EDIT],
            $managePagesNew[self::ACCESS_DELETE],
            $managePagesNew[self::ACCESS_REVIEW],
            $managePagesNew[self::ACCESS_PUBLISH],
            $managePagesNew[self::ACCESS_DELETE_PUBLISH],
            $managePagesNew[self::ACCESS_DELETE_PUBLISH_SELF],
            // users
            $manageUsersNew[self::EDIT_ADMIN_SUPER],
            $manageUsersNew[self::CHANGE_ROLE_ADMIN_SUPER],
            $manageUsersNew[self::CREATE_STATUS_ADMIN_SUPER],
            $manageUsersNew[self::DELETE_ADMIN_SUPER],
            // medias
            $manageMediasNew[self::ACCESS_READ],
            $manageMediasNew[self::ACCESS_EDIT_SELF],
            $manageMediasNew[self::ACCESS_DELETE_SELF]
        );

        $manageMediasNewContrib = $manageMediasNew;
        unset($manageMediasNewContrib[self::ACCESS_READ]);

        return [
            self::ROLE_BANNED => [
                self::MANAGE_PUBLIC => self::LIST[self::MANAGE_PUBLIC]
            ],
            self::GUEST => [
                self::MANAGE_PUBLIC => self::LIST[self::MANAGE_PUBLIC]
            ],
            self::ROLE_UNKNOWN => [
                self::MANAGE_PUBLIC => self::LIST[self::MANAGE_PUBLIC]
            ],
            self::ROLE_VIP     => [
                self::MANAGE_VIP => self::LIST[self::MANAGE_VIP]
            ],
            self::ROLE_CONTRIBUTOR => [
                self::MANAGE_POST => [
                    self::ACCESS_CREATE,
                    self::ACCESS_READ,
                    self::ACCESS_EDIT_DRAFT_SELF
                ],
                self::MANAGE_MEDIA => $manageMediasNewContrib
            ],
            self::ROLE_AUTHOR => [
                self::MANAGE_POST  => $managePostsNew,
                self::MANAGE_PAGE  => $managePagesNew,
                self::MANAGE_MEDIA => $manageMediasNew,
            ],
            self::ROLE_ADMIN => [
                self::MANAGE_USER => $manageUsersNew,
                self::MANAGE_POST => self::LIST[self::MANAGE_POST],
                self::MANAGE_PAGE => self::LIST[self::MANAGE_PAGE],
                self::MANAGE_SETTING => self::LIST[self::MANAGE_SETTING],
                self::MANAGE_THEME => self::LIST[self::MANAGE_THEME],
                self::MANAGE_MEDIA => self::LIST[self::MANAGE_MEDIA],
            ],
            self::ROLE_SUPER_ADMIN => [
                self::MANAGE_USER => self::LIST[self::MANAGE_USER]
            ]
        ];
    }
}
