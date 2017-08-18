<?php
namespace Pentagonal\Phalcon\Application\Globals\Model;

use Pentagonal\Phalcon\Application\Globals\Library\RoleList;
use Phalcon\Mvc\Model;

/**
 * Class User
 * @package Pentagonal\Phalcon\Application\Globals\Model
 */
class User extends Model
{
    const TABLE_NAME = 'users';

    const ROLE_SUPER_ADMIN    = RoleList::ROLE_SUPER_ADMIN;
    const ROLE_ADMIN          = RoleList::ROLE_ADMIN;
    const ROLE_AUTHOR         = RoleList::ROLE_AUTHOR;
    const ROLE_CONTRIBUTOR    = RoleList::ROLE_CONTRIBUTOR;
    const ROLE_STANDARD       = RoleList::ROLE_STANDARD;
    const ROLE_VIP            = RoleList::ROLE_VIP;
    const ROLE_UNKNOWN        = RoleList::ROLE_UNKNOWN;
    const GUEST               = RoleList::GUEST;
    const ROLE_SUBSCRIBER     = RoleList::ROLE_SUBSCRIBER;
    const ROLE_BANNED         = RoleList::ROLE_BANNED;


    const ROLE_AUTHOR_PENDING        = self::ROLE_AUTHOR . ':pending';
    const ROLE_AUTHOR_PENDING_REVIEW = self::ROLE_AUTHOR . ':pending:review';
    const ROLE_AUTHOR_PENDING_ACTIVATION = self::ROLE_AUTHOR . ':pending:activation';

    const ROLE_CONTRIBUTOR_PENDING        = self::ROLE_CONTRIBUTOR . ':pending';
    const ROLE_CONTRIBUTOR_PENDING_REVIEW = self::ROLE_CONTRIBUTOR . ':pending:review';
    const ROLE_CONTRIBUTOR_PENDING_ACTIVATION = self::ROLE_CONTRIBUTOR . ':pending:activation';

    const ROLE_STANDARD_PENDING            = self::ROLE_STANDARD . ':pending';
    const ROLE_STANDARD_PENDING_REVIEW     = self::ROLE_STANDARD . ':pending:review';
    const ROLE_STANDARD_PENDING_ACTIVATION = self::ROLE_STANDARD . ':pending:activation';

    const ROLE_VIP_PENDING            = self::ROLE_VIP . ':pending';
    const ROLE_VIP_PENDING_REVIEW     = self::ROLE_VIP . ':pending:review';
    const ROLE_VIP_PENDING_ACTIVATION = self::ROLE_VIP . ':pending:activation';

    const ROLE_BANNED_AUTHOR_PENDING        = self::ROLE_BANNED . ':' . self::ROLE_CONTRIBUTOR_PENDING;
    const ROLE_BANNED_AUTHOR_PENDING_REVIEW = self::ROLE_BANNED . ':' . self::ROLE_CONTRIBUTOR_PENDING_REVIEW;
    const ROLE_BANNED_AUTHOR_PENDING_ACTIVATION = self::ROLE_BANNED . ':' . self::ROLE_CONTRIBUTOR_PENDING_ACTIVATION;

    const ROLE_BANNED_CONTRIBUTOR_PENDING        = self::ROLE_BANNED . ':' . self::ROLE_CONTRIBUTOR_PENDING;
    const ROLE_BANNED_CONTRIBUTOR_PENDING_REVIEW = self::ROLE_BANNED . ':' . self::ROLE_CONTRIBUTOR_PENDING_REVIEW;
    const ROLE_BANNED_CONTRIBUTOR_PENDING_ACTIVATION = self::ROLE_BANNED
                                                       . ':' . self::ROLE_CONTRIBUTOR_PENDING_ACTIVATION;

    const ROLE_BANNED_STANDARD_PENDING            = self::ROLE_BANNED . ':' . self::ROLE_STANDARD_PENDING;
    const ROLE_BANNED_STANDARD_PENDING_REVIEW     = self::ROLE_BANNED . ':' . self::ROLE_STANDARD_PENDING_REVIEW;
    const ROLE_BANNED_STANDARD_PENDING_ACTIVATION = self::ROLE_BANNED . ':' . self::ROLE_STANDARD_PENDING_ACTIVATION;

    const ROLE_BANNED_VIP_PENDING            = self::ROLE_BANNED . ':' . self::ROLE_VIP_PENDING;
    const ROLE_BANNED_VIP_PENDING_REVIEW     = self::ROLE_BANNED . ':' . self::ROLE_VIP_PENDING_REVIEW;
    const ROLE_BANNED_VIP_PENDING_ACTIVATION = self::ROLE_BANNED . ':' . self::ROLE_VIP_PENDING_ACTIVATION;

    const ROLE_BANNED_UNKNOWN        = self::ROLE_BANNED . ':' . self::ROLE_UNKNOWN;
    const ROLE_BANNED_SUBSCRIBER     = self::ROLE_BANNED . ':' . self::ROLE_SUBSCRIBER;

    /**
     * @return array
     */
    public static function baseRoleList() : array
    {
        return [
            'ROLE_SUPER_ADMIN'    => self::ROLE_SUPER_ADMIN,
            'ROLE_ADMIN'          => self::ROLE_ADMIN,
            'ROLE_AUTHOR'         => self::ROLE_AUTHOR,
            'ROLE_CONTRIBUTOR'    => self::ROLE_CONTRIBUTOR,
            'ROLE_STANDARD'       => self::ROLE_STANDARD,
            'ROLE_VIP'            => self::ROLE_VIP,
            'ROLE_UNKNOWN'        => self::ROLE_UNKNOWN,
            'ROLE_SUBSCRIBER'     => self::ROLE_SUBSCRIBER,
            'ROLE_BANNED'         => self::ROLE_BANNED,
        ];
    }

    /**
     * @return bool
     */
    public function isSuperAdmin() : bool
    {
        $role = trim($this->getRole(), ':');
        return $role == self::ROLE_SUPER_ADMIN;
    }

    /**
     * super admin is also admin
     *
     * @return bool
     */
    public function isAdmin() : bool
    {
        $role = trim(strtolower($this->getRole()), ':');
        return $this->isSuperAdmin() || $role == self::ROLE_ADMIN;
    }

    /**
     * @return string
     */
    public function getBaseRole() : string
    {
        if (trim((string) $this->getRole(), ':') == 'banned') {
            return self::ROLE_BANNED;
        }

        if ($this->isSuperAdmin()) {
            return self::ROLE_SUPER_ADMIN;
        }
        if ($this->isAdmin()) {
            return self::ROLE_ADMIN;
        }
        if ($this->isAuthor()) {
            return self::ROLE_AUTHOR;
        }
        if ($this->isContributor()) {
            return self::ROLE_AUTHOR;
        }
        if ($this->isVip()) {
            return self::ROLE_VIP;
        }
        if ($this->isStandard()) {
            return self::ROLE_STANDARD;
        }
        if ($this->isSubscriber()) {
            return self::ROLE_SUBSCRIBER;
        }

        return self::ROLE_UNKNOWN;
    }

    /**
     * @return bool
     */
    public function isAuthor() : bool
    {
        $role = trim((string) $this->getRole(), ':');
        return ! $this->isAdmin() && (bool) preg_match('#:author([\:]+(.+))?$#', $role);
    }

    /**
     * @return bool
     */
    public function isContributor() : bool
    {
        $role = trim((string) $this->getRole(), ':');
        return ! $this->isAdmin() && (bool) preg_match('#:contributor([\:]+(.+))?$#', $role);
    }

    /**
     * @return bool
     */
    public function isVip() : bool
    {
        $role = trim($this->getRole(), ':');
        return ! $this->isAdmin() && !$this->isAuthor() && (bool) preg_match('#\:vip([\:]+(.+))?$#', $role);
    }

    /**
     * @return bool
     */
    public function isStandard() : bool
    {
        $role = trim($this->getRole(), ':');
        return ! $this->isAdmin() && !$this->isAuthor() && (bool) preg_match('#\:standard([\:]+(.+))?$#', $role);
    }

    /**
     * @return bool
     */
    public function isBanned() : bool
    {
        $role = trim($this->getRole(), ':');
        return $role == self::ROLE_BANNED ||
               ! $this->isAdmin() && (bool) preg_match('#banned([\:]+(.+))?$#', $role);
    }

    /**
     * @return bool
     */
    public function isSubscriber() : bool
    {
        $role = trim($this->getRole(), ':');
        return ! $this->isAdmin() && !$this->isAuthor() && stripos($role, 'subscriber') !== false;
    }

    /**
     * @return bool
     */
    public function isUnknown() : bool
    {
        return $this->getBaseRole() == self::ROLE_UNKNOWN;
    }

    /**
     * @return bool
     */
    public function isPending() : bool
    {
        $role = trim((string) $this->getRole(), ':');
        return ! $this->isAdmin() && (bool) preg_match('#(.+)\:pending(\:.+)?$#', $role);
    }

    /**
     * @return bool
     */
    public function isPendingActivation() : bool
    {
        $role = trim((string) $this->getRole(), ':');
        if ($this->isPending()) {
            return (bool) preg_match('#(.+)\:activation(\:.+)?$#', $role);
        }

        return (bool) preg_match('#(.+)\:activation(\:.+)?$#', $role);
    }

    /**
     * @return bool
     */
    public function isPendingReview() : bool
    {
        $role = trim((string) $this->getRole(), ':');
        if ($this->isPending()) {
            return (bool) preg_match('#(.+)\:review(\:.+)?$#', $role);
        }

        return (bool) preg_match('#(.+)\:review(\:.+)?$#', $role);
    }

    /**
     * @return bool
     */
    public function isPendingAuthor() : bool
    {
        return $this->isAuthor() && $this->isPending();
    }

    /**
     * @return bool
     */
    public function isPendingActivationAuthor() : bool
    {
        return $this->isAuthor() && $this->isPendingActivation();
    }

    /**
     * @return bool
     */
    public function isPendingReviewAuthor() : bool
    {
        return $this->isAuthor() && $this->isPendingActivation();
    }

    /**
     * @return bool
     */
    public function isPendingContributor() : bool
    {
        return $this->isContributor() && $this->isPending();
    }

    /**
     * @return bool
     */
    public function isPendingActivationContributor() : bool
    {
        return $this->isContributor() && $this->isPendingActivation();
    }

    /**
     * @return bool
     */
    public function isPendingReviewContributor() : bool
    {
        return $this->isContributor() && $this->isPendingActivation();
    }

    /**
     * @return bool
     */
    public function isPendingVip() : bool
    {
        return $this->isVip() && $this->isPending();
    }

    /**
     * @return bool
     */
    public function isPendingActivationVip() : bool
    {
        return $this->isVip() && $this->isPendingActivation();
    }

    /**
     * @return bool
     */
    public function isPendingReviewVip() : bool
    {
        return $this->isVip() && $this->isPendingReview();
    }

    /**
     * @return bool
     */
    public function isPendingStandard() : bool
    {
        return $this->isStandard() && $this->isPending();
    }

    /**
     * @return bool
     */
    public function isPendingActivationStandard() : bool
    {
        return $this->isStandard() && $this->isPendingActivation();
    }

    /**
     * @return bool
     */
    public function isPendingReviewStandard() : bool
    {
        return $this->isStandard() && $this->isPendingReview();
    }

    /**
     * @return bool
     */
    public function isBannedAuthor() : bool
    {
        return $this->isAuthor() && $this->isBanned();
    }

    /**
     * @return bool
     */
    public function isBannedVip() : bool
    {
        return $this->isVip() && $this->isBanned();
    }

    /**
     * @return bool
     */
    public function isBannedStandard() : bool
    {
        return $this->isStandard() && $this->isBanned();
    }

    /**
     * @return bool
     */
    public function isBannedSubscriber() : bool
    {
        return $this->isSubscriber() && $this->isBanned();
    }

    /**
     * @return bool
     */
    public function isBannedUnknown() : bool
    {
        return $this->isBanned() && $this->isUnknown();
    }

    /**
     * @return User
     */
    public function initialize() : User
    {
        /**
         * @var User $model
         */
        $model = $this->setSource(self::TABLE_NAME);
        return $model;
    }

    /**
     * @param string $username
     *
     * @return string
     */
    public function validateUsername(string $username) : string
    {
        if (strlen($username) > 120) {
            throw new \LogicException(
                sprintf(
                    'Username must be less or equals of 120 characters length %d given.',
                    strlen($username)
                ),
                E_USER_WARNING
            );
        }
        if (strlen($username) < 3) {
            throw new \LogicException(
                sprintf(
                    'Username must be more or equals of 3 characters length %d given.',
                    strlen($username)
                ),
                E_USER_WARNING
            );
        }

        if (preg_match('/[^a-z0-9]/i', $username)) {
            throw new \LogicException(
                'Username is invalid. Username only contain alpha numeric characters',
                E_USER_WARNING
            );
        }

        return $username;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $this->validateUsername($username);
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @param string $first_name
     */
    public function setFirstName(string $first_name)
    {
        $first_name = preg_replace('/\s+/', ' ', $first_name);
        $first_name = trim($first_name);
        if (strlen($first_name) === 0) {
            throw new \LogicException(
                'Fist name could not be empty or just whitespace only',
                E_USER_WARNING
            );
        }
        if (strlen($first_name) > 120) {
            throw new \LogicException(
                sprintf(
                    'First name must be less or equals of 120 characters length %d given.',
                    strlen($first_name)
                ),
                E_USER_WARNING
            );
        }

        $this->first_name = $first_name;
    }

    /**
     * @param string $last_name
     */
    public function setLastName($last_name)
    {
        if (is_string($last_name)) {
            $last_name = preg_replace('/\s+/', ' ', $last_name);
            $last_name = trim($last_name);
            if (strlen($last_name) > 120) {
                throw new \LogicException(
                    'Last name must be less or equals of 120 characters length.',
                    E_USER_WARNING
                );
            }
        } elseif (is_null($last_name) || is_bool($last_name)) {
            $last_name = null;
        } else {
            throw new \LogicException(
                sprintf(
                    'Last name must be as a string %s given.',
                    gettype($last_name)
                ),
                E_USER_WARNING
            );
        }

        $this->last_name = $last_name;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role)
    {
        $this->role = trim(strtolower($role));
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getRole() : string
    {
        return strtolower($this->role);
    }

    /**
     * @return string
     */
    public function getTokenKey(): string
    {
        return $this->token_key;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        if ($this->getId() === 1) {
            throw new \RuntimeException(
                "Could not delete your current user!"
            );
        }

        return true;
    }
}
