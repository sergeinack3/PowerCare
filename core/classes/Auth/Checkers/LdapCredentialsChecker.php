<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Checkers;

use Ox\Core\Auth\Exception\CredentialsCheckException;
use Ox\Core\Auth\User;
use Ox\Core\CMbException;
use Ox\Core\Config\Conf;
use Ox\Mediboard\Admin\CLDAP;
use Ox\Mediboard\Admin\CLDAPNoSourceAvailableException;
use Ox\Mediboard\Admin\CMbInvalidCredentialsException;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CUserAuthentication;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Will check the credentials of a given user against LDAP.
 */
class LdapCredentialsChecker implements CredentialsCheckerInterface
{
    use CredentialsCheckerTrait;

    /** @var Conf */
    private $conf;

    /** @var CLDAP */
    private $ldap_provider;

    /**
     * @param Conf  $conf
     * @param CLDAP $ldap_provider
     */
    public function __construct(Conf $conf, CLDAP $ldap_provider)
    {
        $this->conf          = $conf;
        $this->ldap_provider = $ldap_provider;
        $this->method        = CUserAuthentication::AUTH_METHOD_LDAP;
    }

    public function check(string $password, UserInterface $user): bool
    {
        $this->setLogMethod();

        if (!$user instanceof User) {
            return false;
        }

        $ox_user         = $user->getOxUser();
        $ldap_connection = (bool)$this->conf->get('admin LDAP ldap_connection');

        if (!$ldap_connection || $ox_user->getLdapUid() === null) {
            return false;
        }

        try {
            return $this->checkWithLDAP($ox_user->user_username, $password);
        } catch (CLDAPNoSourceAvailableException $e) {
            // No LDAP source available
            return false;
        } catch (CMbInvalidCredentialsException $e) {
            // No login attempts blocking if user is LDAP-bound
            if ($this->increment_badge !== null) {
                $this->increment_badge->disable();
            }

            // In order to interrupt the chain calls
            throw new CredentialsCheckException();
        } catch (CMbException $e) {
            return false;
        }
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     * @throws CLDAPNoSourceAvailableException
     * @throws CMbException
     */
    private function checkWithLDAP(string $username, string $password): bool
    {
        $user_ldap                = new CUser();
        $user_ldap->user_username = $username;
        $user_ldap->loadMatchingObjectEsc();

        $user_ldap->_user_password = $password;
        $user_ldap->_bound         = false;

        $user = $this->ldap_provider->logUser($user_ldap, $user_ldap->getLdapUid());

        return (bool)$user->_bound;
    }
}
