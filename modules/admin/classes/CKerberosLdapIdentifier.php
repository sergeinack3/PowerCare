<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;

/**
 * Description
 */
class CKerberosLdapIdentifier extends CMbObject
{
    /** @var integer Primary key */
    public $kerberos_ldap_identifier_id;

    /** @var string LDAP username */
    public $username;

    /** @var int CUser ID */
    public $user_id;

    /** @var CUser */
    public $_ref_user;

    /**
     * @inheritDoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'kerberos_ldap_identifier';
        $spec->key   = 'kerberos_ldap_identifier_id';

        $spec->uniques['username'] = ['username'];

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['username'] = 'str notNull';
        $props['user_id']  = 'ref class|CUser notNull cascade back|kerberos_ldap_identifiers';

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = $this->username;
    }

    /**
     * @return CUser|null
     * @throws Exception
     */
    public function loadRefUser(): ?CUser
    {
        return $this->_ref_user = $this->loadFwdRef('user_id');
    }

    /**
     * Find a CUser according to given external username
     *
     * @param string $username The username
     *
     * @return CUser|null
     * @throws Exception
     */
    public static function findUserByName(string $username): ?CUser
    {
        if (!$username || !CAppUI::conf('admin CKerberosLdapIdentifier enable_kerberos_authentication')) {
            return null;
        }

        $identifier           = new static();
        $identifier->username = $username;

        if ($identifier->loadMatchingObjectEsc()) {
            return $identifier->loadRefUser();
        }

        return null;
    }

    /**
     * @param string $username
     * @param string $user_id
     *
     * @return static|null
     * @throws Exception
     */
    public static function create(string $username, string $user_id): ?self
    {
        if (
            !$username
            || !$user_id
            || !CAppUI::conf('admin CKerberosLdapIdentifier enable_kerberos_authentication')
        ) {
            return null;
        }

        $identifier           = new static();
        $identifier->username = $username;
        $identifier->user_id  = $user_id;

        if ($identifier->loadMatchingObjectEsc()) {
            return $identifier;
        }

        $identifier->store();

        if ($identifier && $identifier->_id) {
            return $identifier;
        }

        return null;
    }

    /**
     * Return true is the SSO login button is enabled.
     *
     * @return bool
     * @throws Exception
     */
    public static function isLoginButtonEnabled(): bool
    {
        return (
            CAppUI::conf('admin CKerberosLdapIdentifier enable_kerberos_authentication')
            &&
            CAppUI::conf('admin CKerberosLdapIdentifier enable_login_button')
        );
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function isReady(): bool
    {
        $self = new static();

        return $self->isInstalled();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function automappingEnabled(): bool
    {
        return
            self::isReady()
            && CAppUI::conf('admin CKerberosLdapIdentifier enable_kerberos_authentication')
            && CAppUI::conf('admin CKerberosLdapIdentifier enable_automapping');
    }
}
