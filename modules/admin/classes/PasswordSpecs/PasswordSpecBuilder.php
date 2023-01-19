<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\PasswordSpecs;

use Exception;
use Ox\Core\CAppUI;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Description
 */
class PasswordSpecBuilder
{
    /** @var CUser|CMediusers */
    private $user;

    /** @var PasswordSpecConfiguration */
    private $configuration;

    /** @var string */
    private $username_field;

    /**
     * PasswordSpecBuilder constructor.
     *
     * @param CUser|CMediusers               $user
     * @param PasswordSpecConfiguration|null $configuration
     *
     * @throws Exception
     */
    public function __construct($user, ?PasswordSpecConfiguration $configuration = null)
    {
        if ($user instanceof CUser) {
            $this->username_field = 'user_username';
        } elseif ($user instanceof CMediusers) {
            $this->username_field = '_user_username';
        } else {
            throw new Exception('Invalid user type');
        }

        $this->user          = $user;
        $this->configuration = ($configuration) ?: self::createConfiguration();
    }

    public function getWeakSpec(): PasswordSpec
    {
        return $this->buildWeak();
    }

    public function getStrongSpec(): PasswordSpec
    {
        return $this->buildStrong();
    }

    public function getLDAPSpec(): PasswordSpec
    {
        return $this->buildLDAP();
    }

    public function getAdminSpec(): PasswordSpec
    {
        return $this->buildAdmin();
    }

    public function getConfiguration(): PasswordSpecConfiguration
    {
        return $this->configuration;
    }

    /**
     * @return PasswordSpec
     * @throws Exception
     */
    public function build(): PasswordSpec
    {
        $is_ldap = $this->isUserLDAPLinked();

        if ($is_ldap) {
            return $this->buildLDAP();
        }

        // No strong password
        if (!$this->configuration['strong_password']) {
            return $this->buildWeak();
        }

        $is_admin = $this->isUserAdmin();

        // Strong password for admin, not checking remote field
        if ($this->configuration['admin_specific'] && $is_admin) {
            return $this->buildAdmin();
        }

        // Strong password for everyone
        if ($this->configuration['apply_all_users']) {
            return $this->buildStrong();
        }

        // Strong password only for remote users
        $has_remote_access_allowed = $this->hasUserRemoteAccessAllowed();

        if ($has_remote_access_allowed) {
            return $this->buildStrong();
        }

        return $this->buildWeak();
    }

    /**
     * @return PasswordSpec
     * @throws Exception
     */
    private function buildWeak(): PasswordSpec
    {
        return PasswordSpec::createWeak(get_class($this->user), 4);
    }

    /**
     * @return PasswordSpec
     * @throws Exception
     */
    private function buildStrong(): PasswordSpec
    {
        $min_length = $this->configuration['strong_min_length'];
        $alpha      = $this->configuration['strong_alpha_chars'];
        $upper      = $this->configuration['strong_upper_chars'];
        $nums       = $this->configuration['strong_num_chars'];
        $special    = $this->configuration['strong_special_chars'];

        return PasswordSpec::createStrong(
            get_class($this->user),
            $min_length,
            $alpha,
            $upper,
            $nums,
            $special,
            $this->username_field,
            $this->username_field
        );
    }

    /**
     * @return PasswordSpec
     * @throws Exception
     */
    private function buildLDAP(): PasswordSpec
    {
        return PasswordSpec::createLDAP(get_class($this->user), 4);
    }

    /**
     * @return PasswordSpec
     * @throws Exception
     */
    private function buildAdmin(): PasswordSpec
    {
        $min_length = $this->configuration['admin_min_length'];
        $alpha      = $this->configuration['admin_alpha_chars'];
        $upper      = $this->configuration['admin_upper_chars'];
        $nums       = $this->configuration['admin_num_chars'];
        $special    = $this->configuration['admin_special_chars'];

        return PasswordSpec::createAdmin(
            get_class($this->user),
            $min_length,
            $alpha,
            $upper,
            $nums,
            $special,
            $this->username_field,
            $this->username_field
        );
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function isUserAdmin(): bool
    {
        if ($this->user instanceof CUser) {
            return $this->user->isTypeAdmin();
        }

        if ($this->user instanceof CMediusers) {
            return $this->user->isAdmin();
        }

        throw new Exception('Invalid user type');
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function isUserLDAPLinked(): bool
    {
        if ($this->user instanceof CUser) {
            return $this->user->isLDAPLinked();
        }

        if ($this->user instanceof CMediusers) {
            return $this->user->isLDAPLinked();
        }

        throw new Exception('Invalid user type');
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function hasUserRemoteAccessAllowed(): bool
    {
        // Local access only, by default
        $default_remote = false;

        // Remote field value is inverted: TRUE means local access only
        if ($this->user instanceof CMediusers) {
            if ($this->user->remote !== null) {
                return !$this->user->remote;
            }

            return $default_remote;
        }

        if ($this->user instanceof CUser) {
            if ($this->user->_ref_mediuser) {
                if ($this->user->_ref_mediuser->remote !== null) {
                    return !$this->user->_ref_mediuser->remote;
                }

                return $default_remote;
            }

            $mediuser = new CMediusers();
            if ($mediuser->isInstalled()) {
                if ($mediuser->load($this->user->_id)) {
                    if ($mediuser->remote !== null) {
                        return !$mediuser->remote;
                    }

                    return $default_remote;
                }
            }

            return $default_remote;
        }

        throw new Exception('Invalid user type');
    }

    /**
     * For testability purposes.
     *
     * @return PasswordSpecConfiguration
     * @throws Exception
     */
    private static function createConfiguration(): PasswordSpecConfiguration
    {
        static $configuration = null;

        if ($configuration instanceof PasswordSpecConfiguration) {
            return $configuration;
        }

        $configuration = new PasswordSpecConfiguration();

        $configuration['strong_password'] = (bool)CAppUI::conf('admin CUser strong_password');
        $configuration['apply_all_users'] = (bool)CAppUI::conf('admin CUser apply_all_users');
        $configuration['admin_specific']  = (bool)CAppUI::conf('admin CUser enable_admin_specific_strong_password');

        $strong_min_length = (int)CAppUI::conf('admin CPasswordSpec strong_password_min_length');
        if ($strong_min_length < 1) {
            $strong_min_length = 6;
        }

        $configuration['strong_min_length']    = $strong_min_length;
        $configuration['strong_alpha_chars']   = (bool)CAppUI::conf('admin CPasswordSpec strong_password_alpha_chars');
        $configuration['strong_upper_chars']   = (bool)CAppUI::conf('admin CPasswordSpec strong_password_upper_chars');
        $configuration['strong_num_chars']     = (bool)CAppUI::conf('admin CPasswordSpec strong_password_num_chars');
        $configuration['strong_special_chars'] = (bool)CAppUI::conf(
            'admin CPasswordSpec strong_password_special_chars'
        );

        $admin_min_length = (int)CAppUI::conf('admin CPasswordSpec admin_strong_password_min_length');
        if ($admin_min_length < 1) {
            $admin_min_length = 6;
        }

        $configuration['admin_min_length']    = $admin_min_length;
        $configuration['admin_alpha_chars']   = (bool)CAppUI::conf(
            'admin CPasswordSpec admin_strong_password_alpha_chars'
        );
        $configuration['admin_upper_chars']   = (bool)CAppUI::conf(
            'admin CPasswordSpec admin_strong_password_upper_chars'
        );
        $configuration['admin_num_chars']     = (bool)CAppUI::conf(
            'admin CPasswordSpec admin_strong_password_num_chars'
        );
        $configuration['admin_special_chars'] = (bool)CAppUI::conf(
            'admin CPasswordSpec admin_strong_password_special_chars'
        );

        return $configuration;
    }
}
