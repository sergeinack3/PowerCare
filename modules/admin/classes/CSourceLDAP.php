<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CRequest;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Source LDAP
 */
class CSourceLDAP extends CMbObject
{
    public $source_ldap_id;

    // DB Fields
    public $name;
    public $host;
    public $port;
    public $rootdn;
    public $bind_rdn_suffix;
    public $ldap_opt_protocol_version;
    public $ldap_opt_referrals;
    public $priority;
    public $secured;

    public $user;
    public $password;

    /** @var bool */
    public $dn_whitelist;

    /** @var bool */
    public $cascade;

    /** @var string */
    public $dn_alternatives;

    public $_groups;
    public $_group_ids;

    /** @var CSourceLDAPLink[] */
    public $_ref_source_ldap_links;

    public $_options = [];
    public $_ldapconn;

    /** @var array */
    public $_dn_alternatives = [];

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'source_ldap';
        $spec->key   = 'source_ldap_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                              = parent::getProps();
        $props["name"]                      = "str notNull";
        $props["host"]                      = "text notNull";
        $props["port"]                      = "num default|389";
        $props["rootdn"]                    = "str notNull";
        $props["bind_rdn_suffix"]           = "str";
        $props["ldap_opt_protocol_version"] = "num default|3";
        $props["ldap_opt_referrals"]        = "bool default|0";
        $props["priority"]                  = "num";
        $props["secured"]                   = "bool default|0";
        $props['user']                      = 'str';
        $props['password']                  = 'password show|0 loggable|0';
        $props['dn_whitelist']              = 'bool notNull default|0';
        $props['dn_alternatives']           = 'text';
        $props['cascade']                   = 'bool default|0 notNull';

        $props['_groups'] = 'set list|';

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_options = [
            "LDAP_OPT_REFERRALS"        => $this->ldap_opt_referrals,
            "LDAP_OPT_PROTOCOL_VERSION" => $this->ldap_opt_protocol_version,
        ];

        $this->_view = "{$this->name} ({$this->host})";

        if ($this->dn_alternatives) {
            $dn_alternatives = explode("\n", $this->dn_alternatives);

            foreach ($dn_alternatives as $_base) {
                $_base = trim($_base);

                if (!$_base) {
                    continue;
                }

                $this->_dn_alternatives[] = $_base;
            }
        }
    }

    public function updateGroupsSpecs(): void
    {
        $spec        = $this->_specs['_groups'];
        $spec_groups = static::getGroupsSpecs();

        foreach ($spec_groups as $_group_id => $_text) {
            $spec->_locales[$_group_id] = $_text;
        }

        $ids              = array_keys($spec->_locales);
        $concatenated_ids = implode('|', $ids);

        $spec->_list = $ids;
        $spec->list  = $concatenated_ids;
        $spec->prop  .= $concatenated_ids;
    }

    public static function getGroupsSpecs()
    {
        $request = new CRequest();
        $group   = new CGroups();

        $request->addSelect('group_id, text');
        $request->addTable($group->_spec->table);

        return $group->_spec->ds->loadHashList($request->makeSelect());
    }

    /**
     * Connect to the LDAP
     *
     * @return resource Link identifier
     * @throws CMbException
     */
    public function ldap_connect()
    {
        if (!function_exists("ldap_connect")) {
            throw new CMbException("CSourceLDAP_ldap-functions-not-available");
        }

        if (!$fp = @fsockopen($this->host, $this->port, $errno, $errstr, 2)) {
            throw new CMbException("CSourceLDAP_unreachable", $this->host);
        }

        fclose($fp);

        $host     = ($this->secured ? "ldaps://" : "") . $this->host;
        $ldapconn = @ldap_connect($host, $this->port);
        if (!$ldapconn) {
            throw new CMbException("CSourceLDAP_no-connexion", $this->host);
        }

        foreach ($this->_options as $_option => $value) {
            ldap_set_option($ldapconn, constant($_option), $value);
        }

        return $ldapconn;
    }

    /**
     * Bind to the LDAP
     *
     * @param resource $ldapconn               [optional]
     * @param string   $ldaprdn                [optional]
     * @param string   $ldappass               [optional]
     * @param boolean  $showInvalidCredentials [optional]
     *
     * @return bool
     * @throws CMbException
     */
    public function ldap_bind(
        $ldapconn = null,
        $ldaprdn = null,
        $ldappass = null,
        $showInvalidCredentials = false
    ): bool {
        if (!$ldapconn) {
            $ldapconn = $this->ldap_connect();
        }

        if ($this->bind_rdn_suffix) {
            $ldaprdn = $ldaprdn . $this->bind_rdn_suffix;
        }

        if ($this->isAlternativeBinding()) {
            // Source user is already a DN (and not an RDN)
            if ($ldaprdn !== $this->user) {
                $ldaprdn = "cn={$ldaprdn},{$this->rootdn}";
            }
        }

        // @ because of ldap_bind emitting a warning and error handler showing the exception (based on error conversion)
        // during the login phase (no preference ui or admin type user yet)
        $ldapbind = @ldap_bind($ldapconn, $ldaprdn, utf8_encode($ldappass));
        $error    = ldap_errno($ldapconn);

        // 49 == LDAP_INVALID_CREDENTIALS
        if (!$showInvalidCredentials && ($error == 49)) {
            $error = $this->get_error_message($ldapconn, false);

            $check_password_expiration = CAppUI::conf('admin LDAP check_ldap_password_expiration');

            // Detect password expiration from login
            if (
                $check_password_expiration
                && $this->checkPasswordExpirationFromError($this->get_error_message($ldapconn))
            ) {
                throw new CMbLDAPPasswordExpiredException('common-error-Password expired', $error);
            }

            throw new CMbInvalidCredentialsException("CSourceLDAP-invalid_credentials", $error);
        }

        if (!$ldapbind) {
            $error = $this->get_error_message($ldapconn);

            throw new CMbException("CSourceLDAP_no-authenticate", $this->host, $ldaprdn, $error);
        }

        return true;
    }

    public function get_error_message($ldapconn, $advanced = true)
    {
        $error = ldap_errno($ldapconn);

        $message = ldap_err2str($error);

        if ($advanced) {
            ldap_get_option($ldapconn, LDAP_OPT_ERROR_STRING, $extended_error);
            $message .= " ($extended_error)";
        }

        return $message;
    }

    /**
     * Get detailed error code
     *
     * @param string $message Error message
     *
     * @url http://stackoverflow.com/a/37188629
     * @url https://ldapwiki.com/wiki/Common%20Active%20Directory%20Bind%20Errors
     *
     * @return null
     */
    public function ldap_parse_extented_error_message($message)
    {
        if (preg_match("/(?<=data\s).*?(?=\,)/", $message, $code)) {
            return $code[0];
        }

        return null;
    }

    public function checkPasswordExpirationFromError($message)
    {
        return (in_array($this->ldap_parse_extented_error_message($message), [532, 773]));
    }

    /**
     * @param array    $entry
     * @param resource $ldapconn [optional]
     * @param string   $dn       [optional]
     *
     * @return bool
     */
    public function ldap_mod_replace($entry, $ldapconn = null, $dn = null)
    {
        if (!$ldapconn) {
            $ldapconn = $this->ldap_connect();
        }

        $ret = ldap_mod_replace($ldapconn, $dn, $entry);

        if (!$ret) {
            $error = $this->get_error_message($ldapconn);
            throw new CMbException("CSourceLDAP-entry_modify_error", $error);
        }

        return true;
    }

    /**
     * Query the LDAP
     *
     * @param resource $ldapconn   LDAP connection identifier
     * @param string   $filter     Search filter
     * @param array    $attributes LDAP attributes
     * @param bool     $unbind     Unbind after the query
     *
     * @return array
     */
    public function ldap_search($ldapconn, $filter, $attributes = [], $unbind = true)
    {
        $attributes = ($attributes) ?: $this->getDefaultSearchAttributes();
        $results    = null;

        if ($this->_dn_alternatives) {
            $results = $this->searchWithMultipleBases($ldapconn, $filter, $attributes);
        } else {
            $ldapsearch = @ldap_search($ldapconn, $this->rootdn, $filter, $attributes);
            if ($ldapsearch) {
                $results = ldap_get_entries($ldapconn, $ldapsearch);
            }
        }

        if ($unbind && $ldapconn) {
            // May trigger an error if resource is already closed ("Closed resource" instead of "ldap link resource")
            @ldap_unbind($ldapconn);
        }

        return $results;
    }

    /**
     * @param $ldapconn
     * @param $filter
     * @param $attributes
     *
     * @return array|null
     */
    private function searchWithMultipleBases($ldapconn, $filter, $attributes): ?array
    {
        $results = null;

        $binds = [];
        $bases = [];

        // Whitelist make alternatives the only ones allowed
        if (!$this->dn_whitelist) {
            $binds[] = $ldapconn;
            $bases[] = $this->rootdn;
        }

        foreach ($this->_dn_alternatives as $_base) {
            $binds[] = $ldapconn;
            $bases[] = $_base;
        }

        $ldapsearch = @ldap_search($binds, $bases, $filter, $attributes);

        if ($ldapsearch) {
            $results = [];
            $counts  = 0;

            foreach ($ldapsearch as $_ldap_search) {
                $_result = ldap_get_entries($ldapconn, $_ldap_search);

                if ($_result['count']) {
                    $counts += $_result['count'];

                    unset($_result['count']);

                    $results = array_merge($results, $_result);
                }
            }

            $results['count'] = $counts;
        }

        return $results;
    }

    public function getDefaultSearchAttributes(): array
    {
        if ($this->isAlternativeBinding()) {
            return [
                'cn',
                'sn',
                'dn',
                'telephonenumber',
                'mail',
                'givenname',
            ];
        }

        return [
            'objectclass',
            'cn',
            'sn',
            'telephonenumber',
            'mail',
            'givenname',
            'distinguishedname',
            'displayname',
            'whencreated',
            'objectguid',
            'useraccountcontrol',
            'badpwdcount',
            'pwdlastset',
            'objectsid',
            'accountexpires',
            'samaccountname',
            'msds-userpasswordexpirytimecomputed',
        ];
    }

    /**
     * Get the DN of a specific user
     *
     * @param string $username Name of the user to get the DN of
     *
     * @return string
     * @throws CMbException
     */
    public function get_dn($username)
    {
        if ($this->isAlternativeBinding()) {
            $results = $this->ldap_search($this->_ldapconn, "(cn=$username)", [], false);
        } else {
            $results = $this->ldap_search($this->_ldapconn, "(samaccountname=$username)", [], false);
        }

        if ($results["count"] > 1) {
            throw new CMbException("CSourceLDAP_too-many-results");
        }

        return $results[0]["dn"];
    }

    /**
     * Start TLS transaction
     *
     * @return void
     */
    public function start_tls()
    {
        ldap_start_tls($this->_ldapconn);
    }

    /**
     * Loads the LDAP source links
     *
     * @return CSourceLDAPLink[]|null
     */
    public function loadRefSourceLDAPLinks()
    {
        $this->_ref_source_ldap_links = $this->loadBackRefs('source_ldap_links');

        // In order to apply links in the form field
        $this->_group_ids = CMbArray::pluck($this->_ref_source_ldap_links, 'group_id');
        $this->_groups    = implode('|', $this->_group_ids);

        return $this->_ref_source_ldap_links;
    }

    /**
     * Get all sources
     *
     * @return CSourceLDAP[]
     */
    public static function loadSources()
    {
        $source = new static();

        return $source->loadList(null, '-priority DESC');
    }

    public function isAvailable($group_id): bool
    {
        return (!$this->_group_ids || (in_array($group_id, $this->_group_ids)));
    }

    /**
     * Todo: Remove this.
     * Tell if the LDAP source is on an alternative binding mode (others LDAP attributes than usual)
     *
     * @return bool
     * @throws Exception
     */
    public function isAlternativeBinding()
    {
        if (!$this->_id) {
            return false;
        }

        static $cache = [];

        if (isset($cache[$this->_id])) {
            return $cache[$this->_id];
        }

        $idex               = new CIdSante400();
        $idex->tag          = 'ldap_alternative_binding';
        $idex->id400        = '1';
        $idex->object_class = $this->_class;
        $idex->object_id    = $this->_id;

        return $cache[$this->_id] = ($idex->loadMatchingObject() !== null);
    }
}
