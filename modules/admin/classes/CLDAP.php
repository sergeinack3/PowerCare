<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * LDAP authentication class
 */
class CLDAP
{
    /**
     * @param CUser       $user
     * @param string|null $ldap_guid
     *
     * @return CUser
     * @throws CLDAPNoSourceAvailableException
     * @throws CMbException
     */
    public function logUser(CUser $user, ?string $ldap_guid): CUser
    {
        return self::login($user, $ldap_guid);
    }

    /**
     * Log a user in
     *
     * @param CUser  $user      The user logging in
     * @param string $ldap_guid The user's LDAP GUID
     *
     * @return CUser The user
     * @throws CLDAPNoSourceAvailableException
     * @throws CMbException
     * @throws Exception
     */
    public static function login(CUser $user, ?string $ldap_guid = null): CUser
    {
        $substitution = false;
        $ldap_expired = false;

        if (!$ldap_guid || $user->_user_password) {
            $chain_ldap = CLDAP::poolConnect($user->_id);

            if ($chain_ldap === false || $chain_ldap->areUnreachable()) {
                throw new CLDAPNoSourceAvailableException('CSourceLDAP_all-unreachable');
            }

            try {
                // Stripping slashes because of non-escaped \, " and ' characters set directly from LDAP
                $user->_bound = $chain_ldap->bind(false, $user->user_username, stripcslashes($user->_user_password));
            } catch (CMbLDAPPasswordExpiredException $e) {
                $user->_bound = true;
                $ldap_expired = true;

                // If user has expired password, the LDAP connection will be lost, so we rebind it with global LDAP user
                $chain_ldap->bind(false);
            }
        } else {
            $chain_ldap = CLDAP::poolConnect(null, CGroups::loadCurrent()->_id);

            if ($chain_ldap === false || $chain_ldap->areUnreachable()) {
                throw new CLDAPNoSourceAvailableException('CSourceLDAP_all-unreachable');
            }

            $user->_bound = $chain_ldap->bind(false);

            $substitution = true;
        }

        // Login succesful
        if ($user->_bound) {
            $user = $chain_ldap->searchAndMap($user);
            $user->_ldap_expired = $ldap_expired;

            CAppUI::$instance->auth_method = ($substitution) ? 'substitution' : 'ldap';
        }

        return $user;
    }

    /**
     * @param      $user_id
     * @param null $group_id
     *
     * @return false|ChainSourceLdap
     * @throws Exception
     */
    public static function poolConnect($user_id, $group_id = null)
    {
        if (!$user_id && !$group_id) {
            return false;
        }

        if (!$group_id) {
            $user = new CUser();
            $user->load($user_id);

            if (!$user || !$user->_id) {
                return false;
            }

            $mediuser = $user->loadRefMediuser();
            $group_id = ($mediuser && $mediuser->_id) ? $mediuser->loadRefFunction()->group_id : null;
        }

        $sources = static::getAvailablePool($group_id);

        $sources_for_cascade = [];

        foreach ($sources as $_source) {
            try {
                $_source->_ldapconn = $_source->ldap_connect();

                // Always add the first one
                $sources_for_cascade[] = $_source;

                if (!$_source->cascade) {
                    break;
                }
            } catch (CMbException $e) {
                // Do not interrupt loop because we need to test the other sources
            }
        }

        if (count($sources_for_cascade) > 0) {
            return new ChainSourceLdap(...$sources_for_cascade);
        }

        return false;
    }

    /**
     * Get available LDAP sources for given CGroups ID.
     *
     * @param $group_id
     *
     * @return CSourceLDAP[]
     * @throws Exception
     */
    private static function getAvailablePool($group_id): array
    {
        $sources = CSourceLDAP::loadSources();
        $pool    = [];

        CStoredObject::massLoadBackRefs($sources, 'source_ldap_links');
        foreach ($sources as $_source) {
            $_source->loadRefSourceLDAPLinks();

            if (!$group_id || $_source->isAvailable($group_id)) {
                $pool[] = $_source;
            }
        }

        return $pool;
    }

    /**
     * Changes a user's password inside the LDAP.
     *
     * @param CUser  $user
     * @param        $old_pass
     * @param        $new_pass
     * @param string $encryption Encryption type: Unicode or MD5 or SHA
     *
     * @return bool
     * @throws CMbException
     */
    public static function changePassword(CUser $user, $old_pass, $new_pass, $encryption = "Unicode")
    {
        if (!in_array($encryption, ["Unicode", "MD5", "SHA"])) {
            return false;
        }

        $chain_ldap = CLDAP::poolConnect($user->_id);

        if ($chain_ldap === false) {
            return false;
        }

        $chain_ldap->startTls();

        try {
            // Stripping slashes because of non-escaped \, " and ' characters set directly from LDAP
            $old_pass = stripcslashes($old_pass);
            $new_pass = stripcslashes($new_pass);
            $bound    = $chain_ldap->bind(false, $user->user_username, $old_pass);
        } catch (CMbLDAPPasswordExpiredException $e) {
            // Keep going
        }

        if (!CAppUI::$instance->_renew_ldap_pwd && !$bound) {
            return false;
        }

        $entry = [];

        switch ($encryption) {
            case "Unicode":
                $entry["unicodePwd"][0] = self::encodeUnicodePassword($new_pass);
                break;

            case "MD5":
                $new_pass              = md5($new_pass);
                $entry["userPassword"] = "\{$encryption\}" . base64_encode(pack("H*", $new_pass));
                break;

            case "SHA":
                $new_pass              = sha1($new_pass);
                $entry["userPassword"] = "\{$encryption\}" . base64_encode(pack("H*", $new_pass));
                break;
        }

        // Because of AD user need to have reset password permissions in order to perform this operation
        $chain_ldap->bind(false);

        $dn = $chain_ldap->getDn($user->user_username);

        return $chain_ldap->ldapModReplace($dn, $entry);
    }

    private static function encodeUnicodePassword($password): string
    {
        $password = "\"$password\"";
        $encoded  = "";

        for ($i = 0; $i < strlen($password); $i++) {
            $encoded .= "{$password[$i]}\000";
        }

        return $encoded;
    }

    /**
     * Search and map a user inside the LDAP
     *
     * @param CUser       $user
     * @param CSourceLDAP $source_ldap
     * @param resource    $ldapconn
     * @param string      $person       [optional]
     * @param string      $filter       [optional]
     * @param boolean     $force_create [optional]
     *
     * @return CUser
     */
    static function searchAndMap(
        CUser       $user,
        CSourceLDAP $source_ldap,
                    $ldapconn,
                    $person = null,
                    $filter = null,
                    $force_create = false,
                    $check_password_expiration = true
    ) {
        if (!$person) {
            $person = $user->user_username;
        }
        $person = utf8_encode($person);
        if (!$filter) {
            $filter = $source_ldap->isAlternativeBinding() ? "(cn={$person})" : "(samaccountname=$person)";
        }

        if ($source_ldap->isAlternativeBinding()) {
            $ldapconn = $source_ldap->ldap_connect();
            $source_ldap->ldap_bind($ldapconn, $source_ldap->user, $source_ldap->password);
        }

        $results = $source_ldap->ldap_search($ldapconn, $filter);
        if (!$results || ($results["count"] == 0)) {
            $user->_bound      = false;
            $user->_count_ldap = 0;

            return $user;
        }

        if ($results["count"] > 1) {
            throw new CMbException("CSourceLDAP_too-many-results");
        }

        $results = $results[0];

        $ldap_uid  = self::getObjectGUID($results, $source_ldap);
        $ldap_user = CUser::loadFromLdapUid($ldap_uid);

        // On sauvegarde le password renseigné
        $user_password  = $user->user_password;
        $_user_password = $user->_user_password;

        // objectguid retrouvé on charge le user
        if ($ldap_user && $ldap_user->_id) {
            $user = new CUser();
            $user->load($ldap_user->_id);
        } else {
            // objectguid non retrouvé on associe à l'user courant l'objectguid
            // Si on est pas en mode création on le recherche
            if (!$force_create) {
                // Suppression du password pour le loadMatchingObject
                $user->user_password  = null;
                $user->_user_password = null;

                $user->loadMatchingObject();
                if (!$user->_id) {
                    throw new CMbException("Auth-failed-user-unknown");
                }
            }
        }

        // Désactivation d'un utilisateur depuis le LDAP
        $user->loadRefMediuser();
        $user_actif = $user->_ref_mediuser->actif;

        $user->_bound = true;
        $user         = self::mapTo($user, $results, $source_ldap);

        // Checking if currently connected (action: update user from LDAP)
        $current_mediuser = CMediusers::get();
        if ($check_password_expiration && (!$current_mediuser || !$current_mediuser->_id)) {
            static::checkPasswordExpiration($results, $source_ldap, $user);
        }

        if ($source_ldap->isAlternativeBinding()) {
            $source_ldap->ldap_bind(null, $user->user_username, stripcslashes($user->_user_password));
        }

        // Save Mediuser variables
        $actif        = $user->_user_actif;
        $deb_activite = $user->_user_deb_activite;
        $fin_activite = $user->_user_fin_activite;

        // Restore User password variables
        $user->user_password  = $user_password;
        $user->_user_password = $_user_password;

        $user->_is_logging = true;

        if (!$user->user_type) {
            $user->user_type = 0;
        }
        // Pas de profil
        $user->template          = 0;
        $user->user_login_errors = 0;

        $user->repair();
        $msg = $user->store();
        if ($msg) {
            throw new CMbException($msg);
        }

        if ($user->_id && CKerberosLdapIdentifier::automappingEnabled()) {
            $dn = $user->user_username;

            if ($source_ldap->bind_rdn_suffix) {
                $dn .= $source_ldap->bind_rdn_suffix;
            }

            CKerberosLdapIdentifier::create($dn, $user->_id);
        }

        if ((!$force_create && !$user->_ref_mediuser->actif && !$user_actif) || ($force_create && !$actif)) {
            throw new CMbException("Auth-failed-user-deactivated");
        }

        // Restore Mediuser variables
        $user->_user_actif        = $actif;
        $user->_user_deb_activite = $deb_activite;
        $user->_user_fin_activite = $fin_activite;
        $user->_count_ldap        = 1;

        if ((!$ldap_user || !$ldap_user->_id) && $ldap_uid) {
            $user->ldap_uid = $ldap_uid;

            if ($msg = $user->store()) {
                throw new CMbException($msg);
            } elseif ($check_password_expiration && ($user->_id == CMediusers::get()->_id)) {
                CAppUI::$instance->_is_ldap_linked = true;
            }
        }

        return $user;
    }

    /**
     * @param array   $values      [optional]
     * @param string  $name
     * @param boolean $single      [optional]
     * @param boolean $utf8_decode [optional]
     *
     * @return string
     */
    static function getValue($values, $name, $single = true, $utf8_decode = true)
    {
        if (array_key_exists($name, $values)) {
            return $single ?
                ($utf8_decode ? utf8_decode($values[$name][0]) : $values[$name][0]) :
                ($utf8_decode ? utf8_decode($values[$name]) : $values[$name]);
        }

        return null;
    }

    /**
     * @param CUser       $user Existing user
     * @param             $values
     * @param CSourceLDAP $source_ldap
     *
     * @return CUser
     * @throws Exception
     */
    private static function mapTo(CUser $user, $values, CSourceLDAP $source_ldap)
    {
        $user->user_username   = $source_ldap->isAlternativeBinding() ? self::getValue($values, 'cn') : self::getValue(
            $values,
            "samaccountname"
        );
        $user->user_first_name = self::getValue($values, "givenname");
        $user->user_last_name  = self::getValue($values, "sn") ? self::getValue($values, "sn") : self::getValue(
            $values,
            "samaccountname"
        );

        if (!$user->user_last_name && $source_ldap->isAlternativeBinding()) {
            $user->user_last_name = self::getValue($values, 'cn');
        }

        $user->user_phone = self::getValue($values, "telephonenumber");
        $user->user_email = self::getValue($values, "mail");

        // Checking phone format, if incorrect, we value the internal phone number field
        if ($user->user_phone && $msg = $user->checkProperty('user_phone')) {
            $user->internal_phone = $user->user_phone;
        }

        $whencreated = null;
        if ($when_created = self::getValue($values, "whencreated")) {
            $whencreated = CMbDT::date(CMbDT::dateTimeFromAD($when_created));
        }

        $accountexpires = null;
        if ($account_expires = self::getValue($values, "accountexpires")) {
            // 1000000000000000000 = 16-11-4769 01:56:35
            if ($account_expires < 1000000000000000000) {
                $accountexpires = CMbDT::date(CMbDT::dateTimeFromLDAP($account_expires));
            }
        }

        // 2 = DISABLED ACCOUNT
        // https://support.microsoft.com/en-us/kb/305144
        $actif = (static::getValue($values, 'useraccountcontrol') & 2) ? 0 : 1;

        $user->loadRefMediuser();
        if ($user->_id) {
            $mediuser                 = $user->_ref_mediuser;
            $mediuser->actif          = $actif;
            $mediuser->deb_activite   = $whencreated;
            $mediuser->fin_activite   = $accountexpires;
            $mediuser->_ldap_store    = true;
            $mediuser->_user_password = null;

            $mediuser->store();
        }

        $user->_user_actif        = $actif;
        $user->_user_deb_activite = $whencreated;
        $user->_user_fin_activite = $accountexpires;

        return $user;
    }

    /**
     * @param array $values
     *
     * @return string
     */
    static function getObjectGUID($values, CSourceLDAP $source_ldap)
    {
        if ($source_ldap->isAlternativeBinding()) {
            return self::getValue($values, 'cn'); // Todo: Not a GUID!
        }

        // Passage en hexadécimal de l'objectguid
        $objectguid = unpack('H*', self::getValue($values, "objectguid", true, false));
        $objectguid = $objectguid[1];

        // Old config that doesn't exist anymore, keep for backward compatibility
        if (CAppUI::conf("admin LDAP object_guid_mode") == "registry") {
            $objectguid = self::convertHexaToRegistry($objectguid);
        }

        return $objectguid;
    }

    static function convertHexaToRegistry($objectguid)
    {
        $first_segment  = substr($objectguid, 4, 4);
        $second_segment = substr($objectguid, 0, 4);
        $third_segment  = substr($objectguid, 8, 4);
        $fourth_segment = substr($objectguid, 12, 4);
        $fifth_segment  = substr($objectguid, 16, 4);
        $sixth_segment  = substr($objectguid, 20, 12);

        $first_segment  = implode("", array_reverse(str_split($first_segment, 2)));
        $second_segment = implode("", array_reverse(str_split($second_segment, 2)));
        $third_segment  = implode("", array_reverse(str_split($third_segment, 2)));
        $fourth_segment = implode("", array_reverse(str_split($fourth_segment, 2)));

        return "$first_segment$second_segment-$third_segment-$fourth_segment-$fifth_segment-$sixth_segment";
    }

    /**
     * Escape the string used in LDAP search in order to avoid
     * "LDAP-injections"
     *
     * @param string $str LDAP search query
     *
     * @return string
     */
    static function escape($str)
    {
        $meta_chars = [
            "\0" => "\\00",
            "\\" => "\\5C",
            "("  => "\\28",
            ")"  => "\\29",
            "*"  => "\\2A",
        ];

        return strtr($str, $meta_chars);
    }

    /**
     * Checking if user's password has expired according to given attributes
     *
     * @param array       $values      LDAP attributes
     * @param CSourceLDAP $source_ldap LDAP source
     * @param CUser       $user        User
     *
     * @return void
     */
    static function checkPasswordExpiration($values, CSourceLDAP $source_ldap, CUser $user)
    {
        if (!CAppUI::conf('admin LDAP check_ldap_password_expiration')) {
            return;
        }

        if (!$values || !$source_ldap || !$source_ldap->_id) {
            return;
        }

        $expiration_date = static::getPasswordExpirationDate($values, $source_ldap);

        if (!$expiration_date) {
            return;
        }

        $expiration_date_threshold = CAppUI::conf('admin CUser coming_password_expiration_threshold');
        $expiration_days           = CMbDT::daysRelative(CMbDT::dateTime(), $expiration_date);

        if ($expiration_date_threshold && ($expiration_days <= $expiration_date_threshold)) {
            CValue::setSessionAbs(CAppUI::PASSWORD_REMAINING_DAYS, $expiration_days);
        }

        if ((CMbDT::dateTime() > $expiration_date) && $user->canChangePassword()) {
            CUser::setPasswordMustChange();
        }
    }

    /**
     * Get maxpwdage LDAP attribute
     *
     * @param array       $values      LDAP attributes
     * @param CSourceLDAP $source_ldap LDAP source
     *
     * @return bool|string
     */
    static function getMaxPasswordAge($values, $source_ldap)
    {
        $max_pwd_age = false;

        $dns = explode(',', $values['dn']);

        $root_dn = array_filter(
            $dns,
            function ($v) {
                return (CMbString::lower(substr($v, 0, 2)) == 'dc');
            }
        );

        if (!$root_dn) {
            return false;
        }

        $root_dn = implode(',', $root_dn);

        // Temporary replacing default root DN
        $prev_root_dn        = $source_ldap->rootdn;
        $source_ldap->rootdn = $root_dn;

        try {
            $ldapconn = $source_ldap->ldap_connect();
            $source_ldap->ldap_bind($ldapconn, $source_ldap->user, $source_ldap->password);
        } catch (Exception $e) {
            $source_ldap->rootdn = $prev_root_dn;

            return false;
        }

        $results = $source_ldap->ldap_search($ldapconn, '(objectclass=domain)', ['maxpwdage'], true);

        if ($results && $results['count'] == 1) {
            $results     = $results[0];
            $max_pwd_age = static::getValue($results, 'maxpwdage', true, false);
        }

        $source_ldap->rootdn = $prev_root_dn;

        return $max_pwd_age;
    }

    /**
     * Get AD password expiration date
     *
     * @param array       $values      LDAP attributes
     * @param CSourceLDAP $source_ldap LDAP source
     *
     * @return bool|string
     */
    static function getPasswordExpirationDate($values, CSourceLDAP $source_ldap)
    {
        if (!$values || !$source_ldap || !$source_ldap->_id) {
            return false;
        }

        if ($source_ldap->isAlternativeBinding()) {
            return false;
        }

        // Use of MS calculation
        $expiry_time = static::getValue($values, 'msds-userpasswordexpirytimecomputed');

        // 0 or (2^63)-1 => Password does not expire
        if ($expiry_time === '0' || $expiry_time === '9223372036854775807') {
            return false;
        }

        if ($expiry_time) {
            return CMbDT::dateTime(CMbDT::dateTimeFromLDAP($expiry_time));
        }

        // Calculation fallback (take the DOMAIN default policy)
        $pwd_last_set = static::getValue($values, 'pwdlastset');

        // Password must be renewed
        if ($pwd_last_set === '0') {
            return true;
        }

        // https://support.microsoft.com/en-us/help/305144/how-to-use-the-useraccountcontrol-flags-to-manipulate-user-account-properties
        $do_not_expire_password = (static::getValue($values, 'useraccountcontrol') & 65536) ? 1 : 0;

        // Password never expires
        if ($do_not_expire_password) {
            return false;
        }

        $max_pwd_age = static::getMaxPasswordAge($values, $source_ldap);

        // Unable to determine maxPwdAge attribute, skipping.
        if ($max_pwd_age === false) {
            return false;
        }

        // Passwords never expire
        if (bcmod($max_pwd_age, '4294967296') === '0') {
            return false;
        }

        /**
         * Add max_pwd_age and pwd_last_set and we get password expiration time in Microsoft's time units.
         * Because max_pwd_age is negative we need to subtract it.
         */
        $pwd_expire = bcsub($pwd_last_set, $max_pwd_age);

        return CMbDT::dateTime(CMbDT::dateTimeFromLDAP($pwd_expire));
    }
}
