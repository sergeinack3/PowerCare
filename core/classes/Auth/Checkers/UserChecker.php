<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Checkers;

use Exception;
use Ox\Core\Auth\Exception\CouldNotValidatePreAuthentication;
use Ox\Core\Auth\User;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\Config\Conf;
use Ox\Core\Module\CModule;
use Ox\Core\Network\IpRangeMatcher;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

/**
 * Checks if given User is allowed to connect
 */
class UserChecker implements UserCheckerInterface
{
    /** @var Conf */
    private $conf;

    /**
     * @param Conf $conf
     */
    public function __construct(Conf $conf)
    {
        $this->conf = $conf;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw CouldNotValidatePreAuthentication::userIsNotSupported();
        }

        $ox_user = $user->getOxUser();

        // User template case
        if ($ox_user->template) {
            throw CouldNotValidatePreAuthentication::userIsATemplate();
        }

        // User is a secondary user (user duplicate)
        if ($ox_user->isSecondary()) {
            throw CouldNotValidatePreAuthentication::userIsSecondary();
        }

        try {
            if ($ox_user->isLocked()) {
                throw new Exception();
            }
        } catch (Throwable $t) {
            throw CouldNotValidatePreAuthentication::userIsLocked();
        }

        // Offline mode for non-admins
        if ($this->conf->get('offline_non_admin') && !$ox_user->isAdmin()) {
            // Todo: Destroy the session
            throw CouldNotValidatePreAuthentication::systemIsOfflineForNonAdmins();
        }

        $remote   = 0;
        $group_id = null;

        if ($this->isMediusersModuleActive()) {
            $mediuser = $this->loadMediuser($ox_user->user_username);

            if ($mediuser && $mediuser->_id) {
                if (!$mediuser->actif) {
                    throw CouldNotValidatePreAuthentication::userIsDeactivated();
                }

                $today = CMbDT::date();
                $deb   = $mediuser->deb_activite;
                $fin   = $mediuser->fin_activite;

                // Check if the user is in his activity period
                if (($deb && $deb > $today) || ($fin && $fin <= $today)) {
                    throw CouldNotValidatePreAuthentication::userAccountHasExpired();
                }

                $remote   = $mediuser->remote;
                $group_id = $mediuser->loadRefFunction()->group_id;
            }
        }

        // CAppUI::isIntranet() hydrates the needed CAppUI::$instance properties
        // Remote=1 means NO remote is allowed...
        if (!$this->isIntranet() && $remote == 1 && !$ox_user->isAdmin()) {
            throw CouldNotValidatePreAuthentication::userHasNoRemoteAccess();
        }

        if ($group_id !== null) {
            $whitelist = $this->conf->getForGroupId('system network ip_address_range_whitelist', $group_id);

            if ($whitelist) {
                $whitelist = explode("\n", $whitelist);

                try {
                    $ip_range_checker = new IpRangeMatcher($whitelist);

                    if (!$ip_range_checker->matches($this->getIp())) {
                        throw new Exception();
                    }
                } catch (Throwable $t) {
                    throw CouldNotValidatePreAuthentication::userHasNoAccessFromThisLocation();
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function checkPostAuth(UserInterface $user)
    {
    }

    protected function isMediusersModuleActive(): bool
    {
        return (bool)CModule::getActive('mediusers');
    }

    protected function loadMediuser(string $username): ?CMediusers
    {
        if (!$username) {
            return null;
        }

        $sibling                = new CUser();
        $sibling->user_username = $username;
        $sibling->loadMatchingObjectEsc();
        $sibling->loadRefMediuser();

        if ($sibling->_ref_mediuser && $sibling->_ref_mediuser->_id) {
            return $sibling->_ref_mediuser;
        }

        return null;
    }

    protected function isIntranet(): bool
    {
        return CAppUI::isIntranet();
    }

    protected function getIp(): string
    {
        return (CAppUI::$instance->proxy) ?: CAppUI::$instance->ip;
    }
}
