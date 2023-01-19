<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Ldap;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CMbException;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Cache Facade for LDAP-related attribute.
 */
class LdapCache
{
    public const USER_KEY     = 'user_ldap-linked';
    public const MEDIUSER_KEY = 'mediuser_ldap-linked';

    private const LAYERS = Cache::INNER_OUTER;
    private const TTL    = 3600;

    /**
     * @param CUser|CMediusers $user
     *
     * @return Cache|null
     */
    /**
     * @param CUser|CMediusers $user
     *
     * @return bool|null
     * @throws Exception
     */
    public static function get(object $user): ?bool
    {
        if ($user->_id === null) {
            throw new CMbException('LdapCache-error-Invalid object provided');
        }

        $cache = self::getUserCache($user, $user->_id);

        return $cache->get();
    }

    /**
     * @param CUser|CMediusers $user
     * @param bool             $value
     *
     * @return bool
     * @throws Exception
     */
    public static function set(object $user, bool $value): bool
    {
        if ($user->_id === null) {
            throw new CMbException('LdapCache-error-Invalid object provided');
        }

        $cache = self::getUserCache($user, $user->_id);

        return $cache->put($value);
    }

    /**
     * @param string|null $id
     *
     * @throws Exception
     */
    public static function invalidateCache(?string $id): void
    {
        if ($id === null) {
            return;
        }

        $user_cache     = self::getCache(self::USER_KEY, $id);
        $mediuser_cache = self::getCache(self::MEDIUSER_KEY, $id);

        $user_cache->rem();
        $mediuser_cache->rem();
    }

    /**
     * @param object $user
     * @param string $id
     *
     * @return Cache
     * @throws Exception
     */
    private static function getUserCache(object $user, string $id): Cache
    {
        if ($user instanceof CUser) {
            return self::getCache(self::USER_KEY, $id);
        } elseif ($user instanceof CMediusers) {
            return self::getCache(self::MEDIUSER_KEY, $id);
        }

        throw new CMbException('LdapCache-error-Invalid object provided');
    }

    /**
     * @param string $prefix
     * @param string $id
     *
     * @return Cache
     * @throws Exception
     */
    private static function getCache(string $prefix, string $id): Cache
    {
        return new Cache($prefix, $id, self::LAYERS, self::TTL);
    }
}
