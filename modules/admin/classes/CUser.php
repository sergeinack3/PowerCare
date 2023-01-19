<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Exception;
use Ox\AppFine\Server\CPatientUser;
use Ox\AppFine\Server\Exception\CAppFineException;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Auth\Badges\IncrementLoginAttemptsBadge;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Core\CPerson;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\Exceptions\CouldNotMerge;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Admin\PasswordSpecs\PasswordSpecBuilder;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;
use Ox\Mediboard\Admin\Rgpd\CRGPDException;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Ccam\CCodageCCAM;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CMergeLog;
use Ox\Mediboard\System\CModuleAction;
use Ox\Mediboard\System\CPreferences;
use Ox\Mediboard\System\CUserAuthentication;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 *  NOTE: the user_type field in the users table must be changed to a TINYINT
 */
class CUser extends CPerson implements ImportableInterface
{
    /** @var string */
    public const USER_PHPUNIT = 'PHPUnit';

    /** @var string */
    public const USER_ADMIN = 'admin';

    /** @var string */
    public const PASSWORD_MUST_CHANGE = 'password_must_change';

    /** @var string */
    public const RESOURCE_TYPE = 'user';

    /** @var string */
    public const RELATION_RGPD = 'rgpd';

    /** @var string */
    public const RELATION_PATIENT_USERS = 'patientUsers';

    /** @var string */
    public const RELATION_APPFINE_FEATURE_LIAISONS = 'appFineFeatureLiaisons';

    /** @var string */
    public const RELATION_ANSWER_RESPONSE = 'secretAnswer';

    /** @var string */
    public const FIELDSET_CONTACT = "contact";

    // DB key
    public $user_id;

    // DB fields
    public $user_username;
    public $user_password;
    public $user_salt;
    public $user_type;
    public $user_first_name;
    public $user_last_name;
    public $user_sexe;
    public $user_email;
    public $user_phone;
    public $user_mobile;
    public $internal_phone;
    public $user_astreinte;
    public $user_astreinte_autre;
    public $user_address1;
    public $user_city;
    public $user_zip;
    public $user_country;
    public $user_birthday;
    public $user_last_login;

    /** @var string Datetime of the last login lock. */
    public $lock_datetime;

    /** @var bool Does the password need to be changed? */
    public $force_change_password;

    /** @var bool Does the user can change its own password? */
    public $allow_change_password;

    /** @deprecated */
    public $user_login_errors;
    public $template;
    public $profile_id;
    public $is_robot;
    public $dont_log_connection;
    public $user_password_last_change;

    public $ldap_uid;

    /** @var string|null */
    public $sub_fc;

    // Derived fields
    public $_user_password;
    public $_login_locked;
    public $_ldap_linked;
    public $_user_actif;
    public $_user_cps;
    public $_user_deb_activite;
    public $_user_fin_activite;
    public $_count_connections;
    public $_count_connections_errors;

    public $_is_logging;
    public $_user_salt;
    public $_user_last_login;

    public $_is_changing;

    // Duplicating fields
    public $_duplicate;
    public $_duplicate_username;

    // Form fields
    public $_user_type_view;
    public $_bound;
    public $_ldap_expired;
    public $_count_ldap;

    // Object references
    public $_ref_preferences;

    /** @var CUserAuthentication */
    public $_ref_last_auth;

    /** @var CMediusers */
    public $_ref_mediuser;

    /** @var self[] */
    public $_ref_profiled_users;

    /** @var CKerberosLdapIdentifier[] */
    public $_ref_kerberos_ldap_identifiers;

    /** @var CPatientUser[] */
    public $_ref_patient_users;

    /** @var CPatientUser[] */
    public $_ref_patients;

    /** @var CUserAnswerResponse */
    public $_ref_answer_response;

    /** @var CPatient[] */
    public $_patient_user_ids = [];

    static $types = [
        // DEFAULT USER (nothing special)
        //    0  => "-- Choisir un type",
        // DO NOT CHANGE ADMINISTRATOR INDEX !
        1  => "Administrator",
        // you can modify the terms below to suit your organisation
        2  => "Hotesse",
        3  => "Chirurgien",
        4  => "Anesthésiste",
        5  => "Directeur",
        6  => "Comptable",
        7  => "Infirmière",
        8  => "PMSI",
        9  => "Qualite",
        10 => "Secrétaire",
        12 => "Surveillante de bloc",
        13 => "Médecin",
        14 => "Personnel",
        15 => "Rééducateur",
        16 => "Sage Femme",
        17 => "Pharmacien",
        18 => "Aide soignant",
        19 => "Dentiste",
        20 => "Préparateur",
        21 => "Diététicien",
        22 => "Patient", // AppFine
        23 => "ASSC",
        24 => "IADE",
        25 => "Assistante sociale",
    ];

    static $ps_types = [3, 4, 13, 16, 17, 19];

    private static $cached_users = [];

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec                      = parent::getSpec();
        $spec->table               = 'users';
        $spec->key                 = 'user_id';
        $spec->measureable         = true;
        $spec->uniques["username"] = ["user_username"];
        $spec->uniques["sub_fc"]   = ["sub_fc"];
        $spec->merge_type          = 'check';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props = parent::getProps();

        // Plain fields
        $props["user_username"]             = "str notNull maxLength|80 seekable|begin fieldset|default";
        $props["user_password"]             = "str maxLength|64 show|0 loggable|0";
        $props["user_salt"]                 = "str maxLength|64 show|0 loggable|0";
        $props["user_type"]                 = "num notNull min|0 max|25 default|0 fieldset|default";
        $props["user_first_name"]           = "str maxLength|50 seekable|begin fieldset|default";
        $props["user_last_name"]            = "str notNull maxLength|50 confidential seekable|begin fieldset|default";
        $props["user_sexe"]                 = "enum list|u|f|m default|u fieldset|default";
        $props["user_email"]                = "str maxLength|255 fieldset|contact";
        $props["user_phone"]                = "phone fieldset|contact";
        $props["user_mobile"]               = "phone fieldset|contact";
        $props["internal_phone"]            = "str fieldset|contact";
        $props["user_astreinte"]            = "str";
        $props["user_astreinte_autre"]      = "str";
        $props["user_address1"]             = "str fieldset|contact";
        $props["user_city"]                 = "str maxLength|30 fieldset|contact";
        $props["user_zip"]                  = "str maxLength|11 fieldset|contact";
        $props["user_country"]              = "str maxLength|30 fieldset|contact";
        $props["user_birthday"]             = "birthDate";
        $props["user_last_login"]           = "dateTime"; // To be removed
        $props["user_login_errors"]         = "num notNull min|0 max|100 default|0";
        $props["lock_datetime"]             = "dateTime";
        $props["template"]                  = "bool notNull default|0";
        $props["profile_id"]                = "ref class|CUser back|profiled_users fieldset|extra";
        $props["is_robot"]                  = "bool default|0 fieldset|extra";
        $props["dont_log_connection"]       = "bool default|0";
        $props["user_password_last_change"] = "dateTime notNull";
        $props["force_change_password"]     = "bool default|0 fieldset|extra";
        $props["allow_change_password"]     = "bool default|1";

        $props['ldap_uid'] = 'str';
        $props['sub_fc']   = 'str';

        $props["_user_password"] = $this->getPasswordSpecBuilder()->build()->getProp();

        // Derived fields
        $props["_ldap_linked"]              = "bool";
        $props["_user_type_view"]           = "str";
        $props["_count_connections"]        = "num";
        $props["_count_connections_errors"] = "num";
        $props["_is_logging"]               = "bool";
        $props["_is_changing"]              = "bool";
        $props["_user_salt"]                = "str";
        $props["_login_locked"]             = "bool";
        $props["_user_last_login"]          = "dateTime";

        return $props;
    }

    /**
     * Update the object's specs
     *
     * @throws Exception
     */
    public function updateSpecs(): void
    {
        $spec                           = $this->getPasswordSpecBuilder()->build();
        $this->_props['_user_password'] = $spec->getProp() . ' reported';
        $this->_specs['_user_password'] = $spec->getSpec('_user_password');
    }

    /**
     * @return PasswordSpecBuilder
     * @throws Exception
     */
    public function getPasswordSpecBuilder(): PasswordSpecBuilder
    {
        return new PasswordSpecBuilder($this);
    }

    /**
     * Lazy access to a given user, default is connected user.
     *
     * Todo: Do not check perm here
     *
     * @param int|null $user_id The user id, connected user if NULL.
     *
     * @return CUser
     * @throws Exception
     */
    public static function get($user_id = null)
    {
        CApp::failIfPublic();

        $user = new CUser();

        // Do not replace CAppUI::$instance->user_id by CAppUI::$user->_id
        return $user->getCached(CValue::first($user_id, CAppUI::$instance->user_id));
    }

    public function getCached($id)
    {
        if (isset(static::$cached_users[$id])) {
            return self::$cached_users[$id];
        }

        $this->load($id);

        return $this;
    }

    public function isCached($id)
    {
        return isset(self::$cached_users[$id]);
    }

    public function registerCache()
    {
        $class = $this->_class;

        // Object counts
        CMbArray::inc(self::$objectCounts, $class);

        // Cache enabled ?
        if (!self::$useObjectCache) {
            return;
        }

        // if layers ever becomes more than INNER, only test existence on INNER
        if ($this->isCached($this->_id)) {
            CMbArray::inc(self::$cachableCounts, $class);
        }

        self::$cached_users[$this->_id] = $this;
    }

    /**
     * @param int $user_id
     *
     * @return CViewAccessToken
     * @throws Exception
     */
    public static function getAccessToken(int $user_id): CViewAccessToken
    {
        $token = new CViewAccessToken();
        $ds    = CSQLDataSource::get('std');
        $where = [
            'user_id'      => $ds->prepare('= ?', $user_id),
            'restricted'   => "= '1'",
            'purgeable'    => "= '1'",
            'datetime_end' => " " . $ds->prepare('> ?', CMbDT::dateTime()),
        ];

        if (!$token->loadObject($where, 'datetime_start DESC')) {
            $token->user_id        = $user_id;
            $token->params         = "m=api\na=test";
            $token->restricted     = '1';
            $token->purgeable      = '1';
            $token->datetime_start = CMbDT::dateTime();

            $lifetime            = 24;
            $token->datetime_end = CMbDT::dateTime("+{$lifetime} hours");
            if ($msg = $token->store()) {
                throw new CAppFineException(
                    CAppFineException::INVALID_STORE,
                    Response::HTTP_CONFLICT,
                    $msg
                );
            }
        }

        return $token;
    }

    /**
     * Is the user a robot?
     *
     * @return bool
     */
    function isRobot()
    {
        if (!$this->_id) {
            return false;
        }

        return $this->is_robot;
    }

    /**
     * Tell if the user has type Administrator
     *
     * @return bool
     */
    function isTypeAdmin()
    {
        return ($this->user_type === '1');
    }

    /**
     * @see parent::loadView()
     */
    function loadView()
    {
        parent::loadView();
        $this->loadRefMediuser();
        $this->_ref_mediuser->loadView();
    }

    /**
     * @inheritDoc
     */
    public function getPerm($permType)
    {
        if (!$this->_id) {
            return parent::getPerm($permType);
        }

        $itself = (CAppUI::$instance && CAppUI::$instance->user_id && CAppUI::$instance->user_id == $this->_id);

        if ($itself) {
            return true;
        }

        return parent::getPerm($permType);
    }

    /**
     * @return CMediusers
     */
    function loadRefMediuser()
    {
        $mediuser = new CMediusers();
        if (CModule::getInstalled("mediusers")) {
            $mediuser            = $mediuser->getCached($this->_id);
            $this->_ref_mediuser = $mediuser;
        }

        return $mediuser;
    }

    /**
     * Return true if user login count system is ready
     *
     * @return boolean
     */
    function loginErrorsReady()
    {
        return $this->isFieldInstalled("user_login_errors");
    }

    /**
     * Return true if new hash system is ready
     *
     * @return boolean
     */
    function loginSaltReady()
    {
        return $this->isFieldInstalled("user_salt");
    }

    /**
     * Randomly create a 64 bytes salt according to available methods
     * Compute user_password by hashing _user_password + user_salt
     *
     * @return void
     */
    function generateUserSalt()
    {
        if ($this->_user_salt) {
            $this->user_salt = $this->_user_salt;
        } else {
            // Instead of Mcrypt, we use mt_rand() method
            $this->user_salt = static::createSalt();
        }

        // DB field to update
        $this->user_password_last_change = CMbDT::dateTime();

        // Compute the hash
        $this->user_password = static::saltPassword($this->user_salt, $this->_user_password);

        CPasswordLog::logPassword($this->user_salt, $this->user_password, $this->_id);
    }

    /**
     * @return string
     */
    public static function createSalt(): string
    {
        return hash("SHA256", mt_rand());
    }

    /**
     * @param string $salt
     * @param string $password
     *
     * @return string
     */
    public static function saltPassword($salt, $password): string
    {
        return hash("SHA256", $salt . $password);
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $user_first_name = CMbString::capitalize($this->user_first_name);
        $user_last_name  = CMbString::upper($this->user_last_name);

        $this->_view = "$user_last_name $user_first_name";

        $this->isLocked();

        $this->_user_type_view = CValue::read(self::$types, $this->user_type);

        $last_name_last_particule   = explode(' ', $this->user_last_name);
        $last_name_last_particule   = ($last_name_last_particule) ? end(
            $last_name_last_particule
        ) : $this->user_last_name;
        $first_name_first_particule = ($this->user_first_name) ? explode(' ', $this->user_first_name) : null;
        $first_name_first_particule = ($first_name_first_particule) ? reset(
            $first_name_first_particule
        ) : $this->user_first_name;
        $this->_shortview           = CMbString::makeInitials(
            "{$first_name_first_particule} {$last_name_last_particule}"
        );

        $this->mapPerson();
    }

    /**
     * Tell whether the user is actually locked.
     *
     * @return bool
     * @throws Exception
     */
    public function isLocked(): bool
    {
        if (!$this->isLockedByAttempts()) {
            return $this->_login_locked = false;
        }

        return $this->_login_locked = $this->isStillLockedByDatetime();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isLockedByAttempts(): bool
    {
        return ($this->user_login_errors >= CAppUI::conf('admin CUser max_login_attempts'));
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isStillLockedByDatetime(): bool
    {
        // No lock datetime field yet, we return previous behaviour.
        if (!$this->isLockDatetimeReady()) {
            return true;
        }

        // If no lock datetime edge case (field is NULL).
        if (!$this->lock_datetime) {
            return true;
        }

        // Computing theoretical unlocking datetime.
        $lock_expiration_time = self::getLockExpirationTime();
        if ($lock_expiration_time <= 0) {
            return true;
        }

        // If theoretical unlocking datetime has been reached, we are not considered as locked anymore.
        return (CMbDT::dateTime("+{$lock_expiration_time} minutes", $this->lock_datetime) > CMbDT::dateTime());
    }

    public static function getLockExpirationtime(): int
    {
        return (int)CAppUI::conf('admin CUser lock_expiration_time');
    }

    public static function getIsLockedQuery(bool $locked): array
    {
        $ds = CSQLDataSource::get('std');

        $where = [];
        if ($locked) {
            $where["users.user_login_errors"] = $ds->prepare('>= ?', CAppUI::conf('admin CUser max_login_attempts'));
            if (($lock_expiration_time = CUser::getLockExpirationtime()) > 0) {
                $where['users.lock_datetime'] = $ds->prepare(
                    '> ?',
                    CMbDT::dateTime("-{$lock_expiration_time} minutes")
                );
            }
        } else {
            $max_login_attempts = CAppUI::conf('admin CUser max_login_attempts');
            if (($lock_expiration_time = CUser::getLockExpirationtime()) > 0) {
                $where[] = $ds->prepare(
                    '(users.user_login_errors < ?1 OR users.lock_datetime < ?2)',
                    $max_login_attempts,
                    CMbDT::dateTime("-{$lock_expiration_time} minutes")
                );
            } else {
                $where["users.user_login_errors"] = $ds->prepare('< ?', $max_login_attempts);
            }
        }

        return $where;
    }

    /**
     * Reset the login errors counter to 0.
     * /!\ Does not store the user.
     */
    public function resetLoginErrorsCounter(bool $store = false): void
    {
        $this->user_login_errors = 0;
        $this->lock_datetime     = '';

        if ($store) {
            $this->store();
        }
    }

    /**
     * @param string|null $datetime
     */
    public function setLockDatetime(?string $datetime = null): void
    {
        $this->lock_datetime = CMbDT::dateTime($datetime);
        $this->store();
    }

    /**
     * Tell whether the lock_datetime field exists.
     *
     * @return bool
     */
    private function isLockDatetimeReady(): bool
    {
        return $this->isFieldInstalled('lock_datetime');
    }

    /**
     * @inheritdoc
     */
    function check()
    {
        // Disable user_type Administrator alteration from a non administrator
        //todo: Prevent password modification too
        if (CAppUI::$instance->user_type !== '1') {
            if (!$this->_id && $this->isTypeAdmin()) {
                return 'CUser-error-You are not allowed to modify an admin user';
            }

            if ($this->fieldModified('user_type')) {
                if ($this->isTypeAdmin()) {
                    return 'CUser-error-You are not allowed to modify an admin user';
                }

                $old_object = $this->loadOldObject();
                if ($old_object && $old_object->_id && $old_object->user_type == '1') {
                    return 'CUser-error-You are not allowed to modify an admin user';
                }
            }
        }

        // Chargement des specs des attributs du mediuser
        $this->updateSpecs();

        $specs = $this->getSpecs();

        // On se concentre sur le mot de passe (_user_password)
        $pwdSpecs = $specs['_user_password'];

        $pwd = $this->_user_password;

        // S'il a été défini, on le contrôle (necessaire de le mettre ici a cause du md5)
        if ($pwd) {
            // minLength
            if ($pwdSpecs->minLength > strlen($pwd)) {
                return "Mot de passe trop court (minimum {$pwdSpecs->minLength})";
            }

            // notContaining
            if (($target = $pwdSpecs->notContaining) && ($field = $this->$target) && stristr($pwd, $field)) {
                return "Le mot de passe ne doit pas contenir '$field'";
            }

            // notNear
            if (($target = $pwdSpecs->notNear) && ($field = $this->$target) && (levenshtein($pwd, $field) < 3)) {
                return "Le mot de passe ressemble trop à '$field'";
            }

            // alphaAndNum
            if ($pwdSpecs->alphaAndNum && (!preg_match("/[A-z]/", $pwd) || !preg_match("/\d+/", $pwd))) {
                return 'Le mot de passe doit contenir au moins un chiffre ET une lettre';
            }

            // alphaLowChars
            if ($pwdSpecs->alphaLowChars && (!preg_match('/[a-z]/', $pwd))) {
                return 'Le mot de passe doit contenir au moins une lettre bas-de-casse (sans disacritique)';
            }

            // alphaUpChars
            if ($pwdSpecs->alphaUpChars && (!preg_match('/[A-Z]/', $pwd))) {
                return 'Le mot de passe doit contenir au moins une lettre en capitale d\'imprimerie (sans accent)';
            }

            // alphaChars
            if ($pwdSpecs->alphaChars && (!preg_match('/[A-z]/', $pwd))) {
                return 'Le mot de passe doit contenir au moins une lettre (sans accent)';
            }

            // numChars
            if ($pwdSpecs->numChars && (!preg_match('/\d/', $pwd))) {
                return 'Le mot de passe doit contenir au moins un chiffre';
            }

            // specialChars
            if ($pwdSpecs->specialChars && (!preg_match('/[!-\/:-@\[-`\{-~]/', $pwd))) {
                return 'Le mot de passe doit contenir au moins un caractère spécial';
            }
        } else {
            $this->_user_password = null;
        }

        return parent::check();
    }

    /**
     * @see parent::store()
     */
    function store()
    {
        $this->updateSpecs();

        if (!$this->_id) {
            $this->user_password_last_change = CMbDT::dateTime();
        }

        $this->generateLockDatetime();

        if ($msg = $this->preparePassword()) {
            return $msg;
        }

        // Keep this before duplicating function
        $store_msg = parent::store();

        $this->duplicateUser();

        return $store_msg;
    }

    /**
     * Automatically set the lock datetime if user has reached max login attempts and no lock datetime is already set.
     *
     * @throws Exception
     */
    private function generateLockDatetime(): void
    {
        if ($this->isLockDatetimeReady() && $this->isLocked() && $this->lock_datetime === null) {
            $this->lock_datetime = CMbDT::dateTime();
        }
    }

    /**
     * Password management method
     *
     * @return string|null
     */
    function preparePassword()
    {
        // To prevent from recalculate new salt and re-hash password
        if ($this->_merging) {
            return null;
        }

        $this->user_password = null;

        // If no raw password or already hashed, nothing to do
        if (!$this->_user_password) {
            return null;
        }

        // If the new password hashing system is not ready yet
        if (!$this->loginSaltReady()) {
            CValue::setSessionAbs('_pass_deferred', $this->_user_password);
            $this->user_password = md5($this->_user_password);

            return null;
        }

        if (!$this->_is_logging || $this->_is_changing) {
            if (!CPasswordLog::isPasswordAllowed($this->_user_password, $this->_id)) {
                return 'CPasswordLog-error-This password has already been used.';
            }

            // If user is logging, get the salt value in table
            $this->generateUserSalt();

            return null;
        }

        // If user is trying to log in, we have to compare hashes with corresponding user in table
        $where = [
            'user_username' => " = '{$this->user_username}'",
        ];

        $_user = new CUser();
        $_user->loadObject($where);

        // If user exists, we compare hashes
        if ($_user->_id) {
            // Password is a SHA256 hash, we get user's salt
            if ($this->_user_password && strlen($_user->user_password) == 64) {
                $this->user_password = hash('SHA256', $_user->user_salt . $this->_user_password);

                return null;
            }

            // Password is an old MD5 hash, we have to update
            if ($_user->user_password == md5($this->_user_password)) {
                $this->generateUserSalt();
                $_user->_user_password = $this->_user_password;
                $_user->_user_salt     = $this->user_salt;
                $_user->store();
            } else {
                // Won't load anything
                $this->user_password = 'dontmatch';
            }
        }

        return null;
    }

    /**
     * Duplicates a CUser (with related CMediusers, CPermObject[], CPermModule[], CPreferences[])
     *
     * @param boolean $set_msg        Wheither to add a message in the system messages pile, or not
     * @param boolean $duplicate_refs Duplicate the user with his refs or without it
     *
     * @return CUser|string|null
     */
    function duplicateUser($set_msg = true, $duplicate_refs = true)
    {
        if (!$this->_id || !$this->_duplicate || !$this->_duplicate_username) {
            return null;
        }

        // CUser duplication
        $old_object_to_duplicate = new self();
        $old_object_to_duplicate->load($this->_id);

        $new_obj = new self();
        $new_obj->cloneFrom($old_object_to_duplicate);

        $new_obj->user_username     = $this->_duplicate_username;
        $new_obj->_user_salt        = null;
        $new_obj->user_salt         = null;
        $new_obj->user_password     = null;
        $new_obj->user_login_errors = null;
        $new_obj->lock_datetime     = null;

        $new_obj->_user_password = CMbSecurity::getRandomPassword();

        // Don't duplicate the new object
        $new_obj->_duplicate          = null;
        $new_obj->_duplicate_username = null;

        // Error occurred
        if ($msg = $new_obj->store()) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);

            return null;
        }

        // CMediusers duplication
        $this->loadRefMediuser();
        if ($this->_ref_mediuser && $this->_ref_mediuser->_id) {
            // If the current user is not an admin the password field is not reseted and the store cannot be completed
            $this->_ref_mediuser->_user_password = null;

            $mediusers = new CMediusers();
            $mediusers->cloneFrom($this->_ref_mediuser);

            $mediusers->user_id        = null;
            $mediusers->_user_id       = $new_obj->_id;
            $mediusers->_user_username = $new_obj->user_username;

            if ($msg = $mediusers->store()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
            }
            $mediusers->duplicateBackRefs($this->_ref_mediuser, "comptes_ch", $mediusers->_id);
        }

        if ($duplicate_refs) {
            // Refs duplication
            $this->duplicateSomeRefs($new_obj);
        }

        if ($set_msg) {
            CAppUI::setMsg('CUser-msg-Object duplicated', UI_MSG_OK);
        }

        return $new_obj;
    }

    /**
     * Duplicates some backprops
     *
     * @param self $user User to get the duplicated refs
     *
     * @return null
     */
    function duplicateSomeRefs($user)
    {
        if (!$this->_id || !$user || !$user->_id) {
            return null;
        }

        // PREFERENCES
        $pref          = new CPreferences();
        $pref->user_id = $this->_id;
        $prefs         = $pref->loadMatchingList();

        foreach ($prefs as $_pref) {
            $new_object = new CPreferences();
            $new_object->cloneFrom($_pref);
            $new_object->user_id = $user->_id;

            if ($msg = $new_object->store()) {
                CAppUI::setMsg($msg, UI_MSG_WARNING);
            }
        }

        // MODULES PERMISSIONS
        $perm_module          = new CPermModule();
        $perm_module->user_id = $this->_id;
        $perms_module         = $perm_module->loadMatchingList();

        foreach ($perms_module as $_perm) {
            $new_object = new CPermModule();
            $new_object->cloneFrom($_perm);
            $new_object->user_id = $user->_id;

            if ($msg = $new_object->store()) {
                CAppUI::setMsg($msg, UI_MSG_WARNING);
            }
        }

        // OBJECTS PERMISSIONS
        $perm_object          = new CPermObject();
        $perm_object->user_id = $this->_id;
        $perm_objects         = $perm_object->loadMatchingList();

        foreach ($perm_objects as $_perm) {
            $new_object = new CPermObject();
            $new_object->cloneFrom($_perm);
            $new_object->user_id = $user->_id;

            if ($msg = $new_object->store()) {
                CAppUI::setMsg($msg, UI_MSG_WARNING);
            }
        }

        return null;
    }

    /**
     * We need to delete the CMediusers
     *
     * @return string
     */
    function delete()
    {
        if ($msg = $this->canDeleteEx()) {
            return $msg;
        }

        $mediuser = $this->loadRefMediuser();

        if ($mediuser->_id) {
            $mediuser->_keep_user = true;
            if ($msg = $mediuser->delete()) {
                return $msg;
            }
        }

        return parent::delete();
    }

    /**
     * @inheritDoc
     */
    public function merge(array $objects, bool $fast, CMergeLog $merge_log): void
    {
        if (!$this->_id) {
            throw CouldNotMerge::baseObjectNotFound();
        }

        // Fast merging obligatoire
        $fast      = true;
        $mediusers = [];

        foreach ($objects as $object) {
            if (!$object->_id) {
                continue;
            }

            $object->loadRefMediuser();
            $mediusers[] = $object->_ref_mediuser;
            $object->removePerms();

            /** @var CSejour $_sejour */
            foreach ($object->_ref_mediuser->loadBackRefs("sejours") as $_sejour) {
                /** @var CCodageCCAM $_codage_ccam */
                foreach ($_sejour->loadRefsCodagesCCAM() as $_codage_ccam) {
                    $_codage_ccam->praticien_id = $this->_id;

                    if ($msg = $_codage_ccam->store()) {
                        $_codage_ccam->delete();
                    }
                }
            }
        }

        $this->loadRefMediuser();
        $this->_ref_mediuser->_force_merge = true;

        $mediuser_merge_log = CMergeLog::logStart(CUser::get()->_id, $this->_ref_mediuser, $mediusers, $fast);

        try {
            $this->_ref_mediuser->merge($mediusers, $fast, $mediuser_merge_log);
            $mediuser_merge_log->logEnd();
        } catch (Throwable $t) {
            $mediuser_merge_log->logFromThrowable($t);

            throw $t;
        }

        parent::merge($objects, $fast, $merge_log);
    }

    function removePerms()
    {
        $this->completeField("user_id");

        // Do not load every perms if not provided...
        if (!$this->user_id) {
            return;
        }

        $perm          = new CPermModule;
        $perm->user_id = $this->user_id;
        $perms         = $perm->loadMatchingList();
        foreach ($perms as $_perm) {
            $_perm->delete();
        }

        $perm          = new CPermObject;
        $perm->user_id = $this->user_id;
        $perms         = $perm->loadMatchingList();
        foreach ($perms as $_perm) {
            $_perm->delete();
        }
    }

    /**
     * @return string error message when necessary, null otherwise
     */
    function copyPermissionsFrom($user_id, $delExistingPerms = false)
    {
        if (!$user_id) {
            return null;
        }

        // Copy user type
        $profile = new CUser();
        $profile->load($user_id);
        $this->user_type = $profile->user_type;
        if ($msg = $this->store()) {
            return $msg;
        }

        // Delete existing permissions
        if ($delExistingPerms) {
            $this->removePerms();
        }

        // Get other user's permissions

        // Module permissions
        $perms = new CPermModule;
        $perms = $perms->loadList("user_id = '$user_id'");

        // Copy them
        foreach ($perms as $perm) {
            $perm->perm_module_id = null;
            $perm->user_id        = $this->user_id;
            $perm->store();
        }

        //Object permissions
        $perms = new CPermObject;
        $perms = $perms->loadList("user_id = '$user_id'");

        // Copy them
        foreach ($perms as $perm) {
            $perm->perm_object_id = null;
            $perm->user_id        = $this->user_id;
            $perm->store();
        }

        return null;
    }

    /**
     * Tell whether user is linked to an LDAP account
     *
     * @return bool
     */
    public function isLDAPLinked(): bool
    {
        if (!CAppUI::conf("admin LDAP ldap_connection") || !$this->_id) {
            return $this->_ldap_linked = false;
        }

        $is_ldap_linked = ($this->getLdapUid() !== null);

        $current_user = CUser::get();

        // If user is not the one currently logged/logging
        if (!$current_user || !$current_user->_id || ($this->_id !== $current_user->_id)) {
            return $this->_ldap_linked = $is_ldap_linked;
        }

        if (CAppUI::$instance->_is_ldap_linked !== null) {
            return $this->_ldap_linked = (bool)CAppUI::$instance->_is_ldap_linked;
        }

        return CAppUI::$instance->_is_ldap_linked = $this->_ldap_linked = $is_ldap_linked;
    }

    /**
     * Count connections for user
     *
     * @return integer
     */
    public function countConnections()
    {
        return $this->_count_connections = $this->countBackRefs("authentications");
    }

    public function countConnectionsErrors()
    {
        return $this->_count_connections_errors = $this->countBackRefs("authentication_errors");
    }

    /**
     * Get the profiled users when this is a template
     *
     * @return array<CUser> Profiled users collection
     */
    function loadRefProfiledUsers()
    {
        return $this->_ref_profiled_users = $this->loadBackRefs("profiled_users", "user_last_name, user_first_name");
    }

    /**
     * @return CStoredObject|CUser
     */
    function loadRefProfiled()
    {
        return $this->loadFwdRef("profile_id");
    }

    /**
     * Tell if a user can change his password
     *
     * @return bool
     */
    function canChangePassword()
    {
        // A bot cannot
        if ($this->isRobot()) {
            return false;
        }

        // Admins can
        if ($this->user_type == 1) {
            return true;
        }

        // Individual change not allowed
        if (!$this->allow_change_password) {
            return false;
        }

        // LDAP-linked account: depending on configuration
        if ($this->isLDAPLinked()) {
            return (bool)CAppUI::conf('admin LDAP allow_change_password');
        }

        // Global change: depending on configuration
        return (bool)CAppUI::conf('admin CUser allow_change_password');
    }

    /**
     * Tell whether a user must change his password
     *
     * @return bool
     */
    public function mustChangePassword()
    {
        return ($this->canChangePassword() && (static::checkPasswordMustChange() || $this->force_change_password));
    }

    /**
     * Tell if password must be changed according to session
     *
     * @return mixed
     */
    static function checkPasswordMustChange()
    {
        return CValue::sessionAbs(static::PASSWORD_MUST_CHANGE);
    }

    /**
     * Empty the variable telling that the password has to be modified
     *
     * @return void
     */
    static function resetPasswordMustChange()
    {
        CValue::setSessionAbs(static::PASSWORD_MUST_CHANGE, null);
    }

    /**
     * Set the changing password variable in session
     *
     * @return void
     */
    static function setPasswordMustChange()
    {
        CValue::setSessionAbs(static::PASSWORD_MUST_CHANGE, true);
    }

    static function checkPassword($username, $password, $return_object = false)
    {
        if (!$password) {
            return ($return_object) ? new CUser() : false;
        }

        $new_user                = new self();
        $new_user->user_username = $username;

        if (!$new_user->loadMatchingObjectEsc()) {
            return ($return_object) ? new CUser() : false;
        }

        if (!CAppUI::$instance->_renew_ldap_pwd && $new_user->isLDAPLinked()) {
            try {
                $chain_ldap = CLDAP::poolConnect($new_user->_id);

                if ($chain_ldap !== false) {
                    // Stripping slashes because of non-escaped \, " and ' characters set directly from LDAP
                    if (
                        !$chain_ldap->bind(false, $new_user->user_username, stripcslashes($password))
                    ) {
                        return ($return_object) ? new CUser() : false;
                    } else {
                        if ($return_object) {
                            return $new_user;
                        }

                        return $new_user->_id != null;
                    }
                }
            } catch (Exception $e) {
                return ($return_object) ? new CUser() : false;
            }
        }

        $new_user                 = new self();
        $new_user->user_username  = $username;
        $new_user->_user_password = $password;
        $new_user->_is_logging    = true;

        $new_user->preparePassword();
        $new_user->loadMatchingObjectEsc();

        if ($return_object) {
            return $new_user;
        }

        return $new_user->_id != null;
    }

    /**
     * Map the class variable with CPerson variable
     *
     * @return void
     */
    function mapPerson()
    {
        $this->_p_city                = $this->user_city;
        $this->_p_postal_code         = $this->user_zip;
        $this->_p_street_address      = $this->user_address1;
        $this->_p_country             = $this->user_country;
        $this->_p_phone_number        = $this->user_phone;
        $this->_p_mobile_phone_number = $this->user_mobile;
        $this->_p_email               = $this->user_email;
        $this->_p_first_name          = $this->user_first_name;
        $this->_p_last_name           = $this->user_last_name;
        $this->_p_birth_date          = $this->user_birthday;
    }

    /**
     * Get last authentication
     *
     * @return CUserAuthentication|null
     */
    function loadRefLastAuth()
    {
        if (!$this->_id || !CUserAuthentication::authReady()) {
            return null;
        }

        $authentications = $this->loadBackRefs("authentications", "datetime_login DESC", 1);
        if (!empty($authentications)) {
            $this->_ref_last_auth = reset($authentications);
        }

        return $this->_ref_last_auth;
    }

    /**
     * Get last authentication date
     *
     * @return string|null
     */
    function getLastLogin()
    {
        if (!$this->_id) {
            return null;
        }

        $cache      = Cache::getCache(Cache::INNER)->withCompressor();
        $last_login = $cache->get('user-last_login-' . $this->_id);

        if ($last_login !== null) {
            return $this->_user_last_login = $last_login;
        }

        $auth = $this->loadRefLastAuth();

        $last_login = null;
        if ($auth && $auth->_id) {
            $last_login = $auth->datetime_login;
        }

        $cache->set('user-last_login-' . $this->_id, $last_login);

        return $this->_user_last_login = $last_login;
    }

    /**
     * get profiles
     *
     * @param array $where Optional conditions
     *
     * @return CUser[]
     */
    static function getProfiles($where = [])
    {
        // Récupération des profils
        $profile           = new CUser();
        $profile->template = 1;

        $where["template"] = "= '1'";

        return $profile->loadList($where);
    }

    /**
     * Checks is given type is a CPatientUser
     *
     * @param integer $type Type to check
     *
     * @return bool
     */
    static function isPatientUser($type)
    {
        if (!$type) {
            return false;
        }

        return (isset(self::$types[$type]) && self::$types[$type] === 'Patient');
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isTypeAdmin();
    }

    /**
     * Checks is given type is a Administrator
     *
     * @param integer $type Type to check
     *
     * @return bool
     */
    static function isAdminUser($type)
    {
        if (!$type) {
            return false;
        }

        return (isset(self::$types[$type]) && self::$types[$type] === 'Administrator');
    }

    /**
     * Charges les liaisons CUser - CPatient
     *
     * @return null|CPatientUser[]
     */
    function loadPatientUsers($order = null, $where = [])
    {
        return $this->_ref_patient_users = $this->loadBackRefs('patient_user', $order, null, null, null, null, null, $where);
    }

    /**
     * @param int $group_id
     *
     * @return CPatientUser|null
     */
    public function loadPatientUser(int $group_id): ?CPatientUser
    {
        $where = [
            'group_id' => "= '$group_id'"
        ];

        $patient_users = $this->loadPatientUsers(null, $where);

        return empty($patient_users) ? null : reset($patient_users);
    }

    /**
     * Charges les liaisons CUser - CPatient pour chaque
     *
     * @return null|CPatientUser[]
     */
    function loadPatients()
    {
        return $this->_ref_patients = $this->loadBackRefs('patient_user', null, null, "patient_id");
    }

    /**
     * Charges les liaisons CUser - CPatient pour chaque
     *
     * @return int[]|null
     * @throws Exception
     */
    public function loadPatientIds($where = [])
    {
        $where = array_merge($where, ['user_id' => " = '$this->_id'"]);

        return (new CPatientUser())->loadColumn('patient_id', $where);
    }

    /**
     * Vérifie l'accessibilité à un patient donné
     *
     * @param integer $patient_id CPatient ID
     *
     * @return bool
     */
    function checkPatientUsers($patient_id = null)
    {
        $cache = new Cache("patient_users", $patient_id, Cache::INNER);

        if ($result = $cache->get()) {
            return $result;
        }

        $_patient_users = $this->loadPatientUsers();

        CStoredObject::massLoadFwdRef($_patient_users, "patient_id");

        foreach ($_patient_users as $_item) {
            $_patient = $_item->loadRefPatient();
            $_patient->loadNamedFile("identite.jpg");

            $this->_patient_user_ids[$_item->patient_id] = $_patient;
        }

        $result = null;

        if (!$patient_id) {
            $result = true;
        } else {
            $result = in_array($patient_id, array_keys($this->_patient_user_ids));
        }

        return $cache->put($result);
    }

    /**
     * Loads active patients
     *
     * @return CPatientUser[]
     */
    function loadActivePatientUsers()
    {
        $_patient_users = $this->loadPatientUsers();

        CStoredObject::massLoadFwdRef($_patient_users, 'patient_id');

        $patient_users = [];
        foreach ($_patient_users as $_item) {
            if (!$_item->active) {
                continue;
            }

            $_patient = $_item->loadRefPatient();
            $_patient->loadNamedFile('identite.jpg');

            $patient_users[$_patient->_id] = $_item;
        }

        return $patient_users;
    }

    /**
     * Loads inactive patients
     *
     * @return array
     */
    function loadInactivePatientUsers()
    {
        $_patient_users = $this->loadPatientUsers();

        CStoredObject::massLoadFwdRef($_patient_users, 'patient_id');

        $patient_users = [];
        foreach ($_patient_users as $_item) {
            if ($_item->active) {
                continue;
            }

            $_patient                      = $_item->loadRefPatient();
            $patient_users[$_patient->_id] = $_item;
        }

        return $patient_users;
    }

    /**
     * Checks whether user is the super administrator
     *
     * @return bool
     */
    function isSuperAdmin()
    {
        return ($this->_id && $this->_id == 1 && $this->user_username === 'admin');
    }

    /**
     * Checks whether current session is an activation session token.
     *
     * @return bool
     * @throws Exception
     */
    public function checkActivationToken(): bool
    {
        if (!$this->_id || !CAppUI::$token_session || !CAppUI::$token_id || !$this->force_change_password) {
            return false;
        }

        try {
            $token = CViewAccessToken::findOrFail(CAppUI::$token_id);
        } catch (Throwable $t) {
            return false;
        }

        $module_action_id = CModuleAction::getID('admin', 'chpwd');

        return ($module_action_id && ($module_action_id === $token->getModuleActionId()));
    }

    /**
     * @return CKerberosLdapIdentifier[]|null
     * @throws Exception
     */
    public function loadRefKerberosLdapIdentifiers()
    {
        return $this->_ref_kerberos_ldap_identifiers = $this->loadBackRefs('kerberos_ldap_identifiers');
    }

    /**
     * @return CStoredObject[]|CPermObject[]
     */
    function loadRefsPermsObject()
    {
        $perms_objects = $this->loadBackRefs('permissions_objet');

        return $perms_objects;
    }


    /**
     * @return CStoredObject[]|CPermModule[]
     */
    function loadRefsPermsModule()
    {
        return $this->loadBackRefs('permissions_module');
    }

    /**
     * Tells whether a user is a primary user or not
     *
     * @return bool
     */
    function isSecondary()
    {
        if (!$this->_id || static::isPatientUser($this->user_type)) {
            return false;
        }

        $mediuser = $this->loadRefMediuser();

        if (!$mediuser || !$mediuser->_id) {
            return false;
        }

        return $mediuser->isSecondary();
    }

    /**
     * @inheritdoc
     */
    function getExportableFields($trads = false)
    {
        $fields = parent::getExportableFields($trads);
        unset($fields['user_password']);
        unset($fields['user_salt']);
        unset($fields['user_password_last_change']);

        return $fields;
    }

    /**
     * @return string
     */
    function getPermModulesHash()
    {
        $perms_module  = $this->loadBackRefs('permissions_module');
        $modules_order = [];

        /** @var CPermModule $_perm */
        foreach ($perms_module as $_perm) {
            if ($_perm->mod_id) {
                $_mod                           = $_perm->loadRefDBModule();
                $modules_order[$_mod->mod_name] = "$_perm->permission|$_perm->view";
            } else {
                $modules_order['all'] = "$_perm->permission|$_perm->view";
            }
        }

        ksort($modules_order);

        return CMbSecurity::hash(CMbSecurity::SHA256, implode('|', $modules_order));
    }

    /**
     * @return string
     */
    function getPermObjectHash()
    {
        $perms_objects = $this->loadBackRefs('permissions_objet');
        $object_order  = [];

        /** @var CPermObject $_perm */
        foreach ($perms_objects as $_perm) {
            if ($_perm->object_id) {
                continue;
            }

            $object_order[$_perm->object_class] = $_perm->permission;
        }

        ksort($object_order);

        return CMbSecurity::hash(CMbSecurity::SHA256, implode('|', $object_order));
    }

    /**
     * @return string
     */
    function getPrefsHash()
    {
        $prefs       = $this->loadBackRefs('preferences');
        $prefs_order = [];

        /** @var CPreferences $_pref */
        foreach ($prefs as $_pref) {
            $prefs_order[$_pref->key] = $_pref->value;
        }

        ksort($prefs_order);

        return CMbSecurity::hash(CMbSecurity::SHA256, implode('|', $prefs_order));
    }

    function loadAnswerResponse()
    {
        // On n'utilise pas le loadUniqueBackRef parce que nous avons des users qui ont plusieurs question secrètes
        $answer_response          = new CUserAnswerResponse();
        $answer_response->user_id = $this->_id;
        $answer_response->loadMatchingObject();

        return $this->_ref_answer_response = $answer_response;
    }

    /**
     * @inheritdoc
     */
    function shouldAskConsent()
    {
        return ($this->_id && !$this->template && !$this->isRobot() && !$this->isSecondary());
    }

    /**
     * @inheritdoc
     */
    function canAskConsent()
    {
        return $this->shouldAskConsent();
    }

    /**
     * Requires user consent
     *
     * @return void
     * @throws CRGPDException
     */
    static function requireUserConsent()
    {
        try {
            $content = CApp::fetch('admin', 'vw_require_user_consent');

            echo $content;
        } catch (Exception $e) {
            throw new CRGPDException($e->getMessage());
        }
    }

    function getPrenomFieldName()
    {
        return 'user_first_name';
    }

    /**
     * @inheritdoc
     */
    function getNomFieldName()
    {
        return 'user_last_name';
    }

    /**
     * @inheritdoc
     */
    function getNaissanceFieldName()
    {
        return 'user_birthday';
    }

    function loadGroupListGuid($where = [], $order = null, $limit = null, $groupby = null, $ljoin = [])
    {
        $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

        $group                                 = CGroups::loadCurrent();
        $where["functions_mediboard.group_id"] = "= '$group->_id'";
        $where["users_mediboard.user_id"]      = " = users.user_id";

        $select = ["users.{$this->_spec->key}", "CONCAT('CUser-', users.{$this->_spec->key})"];
        $ds     = $this->getDS();

        $query = new CRequest();
        $query->addSelect($select);
        $query->addTable(['users', 'users_mediboard']);
        $query->addLJoin($ljoin);
        $query->addWhere($where);
        $query->addOrder($order);
        $query->setLimit($limit);
        $query->addGroup($groupby);

        return $ds->loadHashList($query->makeSelect());
    }

    /**
     * @inheritDoc
     */
    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchUser($this);
    }

    /**
     * @inheritDoc
     */
    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * @return int[]
     * @throws Exception
     */
    public function loadGroupIds(): array
    {
        return (new CPatientUser())->loadColumn('group_id', ['user_id' => " = '$this->_id'"]);
    }

    /**
     * @param int|null $group_id
     *
     * @return CRGPDConsent|null
     * @throws CRGPDException
     */
    public function getRGPD(?int $group_id = null): ?CRGPDConsent
    {
        // Chargement du consentement sur le CUser
        $rgpd_manager = new CRGPDManager($group_id ?? CGroups::loadCurrent()->_id);
        if (!$rgpd_manager->isEnabledFor($this) || !$rgpd_manager->shouldAskConsentFor($this)) {
            return null;
        }
        $user_rgpd_consent = $rgpd_manager->getOrInitConsent($this);

        return $user_rgpd_consent && $user_rgpd_consent->_id ? $user_rgpd_consent : null;
    }

    /**
     * @return Item|null
     * @throws ApiException
     * @throws CRGPDException
     * @throws Exception
     */
    public function getResourceRgpd(): ?Item
    {
        $conf = CAppUI::conf("appFine CRGPDConsent group_id_global_consent");
        if (!$rgpd_consent = $this->getRGPD(intval($conf))) {
            return null;
        }

        return new Item($rgpd_consent);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceAppFineFeatureLiaisons(): ?Collection
    {
        $features_liaisons = $this->loadBackRefs('appfine_feature_user');
        if (!$features_liaisons) {
            return null;
        }

        return new Collection($features_liaisons);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     */
    public function getResourcePatientUsers(): ?Collection
    {
        $patient_users = $this->loadPatientUsers();
        if (!$patient_users) {
            return null;
        }

        return new Collection($patient_users);
    }

    /**
     * @return Item|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceSecretAnswer(): ?Item
    {
        $answerResponse = $this->loadAnswerResponse();
        if (!$answerResponse || !$answerResponse->_id) {
            return null;
        }

        return new Item($answerResponse);
    }

    /**
     * Check password weakness
     */
    public function checkPasswordWeakness(?string $password = null): bool
    {
        $password = ($password) ?: $this->_user_password;

        if ($password === null) {
            return false;
        }

        $spec = $this->getPasswordSpecBuilder()->build()->getSpec('_user_password');

        // minLength
        if ($spec->minLength > strlen($password)) {
            return true;
        }

        // notContaining
        if (
            ($target = $spec->notContaining)
            && ($field = $this->$target)
            && stristr($password, $field)
        ) {
            return true;
        }

        // notNear
        if (
            ($target = $spec->notNear)
            && ($field = $this->$target)
            && (levenshtein($password, $field) < 3)
        ) {
            return true;
        }

        // alphaAndNum
        if (
            $spec->alphaAndNum
            && (!preg_match("/[A-z]/", $password) || !preg_match("/\d+/", $password))
        ) {
            return true;
        }

        // alphaLowChars
        if ($spec->alphaLowChars && (!preg_match('/[a-z]/', $password))) {
            return true;
        }

        // alphaUpChars
        if ($spec->alphaUpChars && (!preg_match('/[A-Z]/', $password))) {
            return true;
        }

        // alphaChars
        if ($spec->alphaChars && (!preg_match('/[A-z]/', $password))) {
            return true;
        }

        // numChars
        if ($spec->numChars && (!preg_match('/\d/', $password))) {
            return true;
        }

        // specialChars
        if ($spec->specialChars && (!preg_match('/[!-\/:-@\[-`\{-~]/', $password))) {
            return true;
        }

        return false;
    }

    public function getLdapUid(): ?string
    {
        if (!$this->_id) {
            return null;
        }

        if ($this->isFieldInstalled('ldap_uid')) {
            return $this->ldap_uid;
        }

        // Idex fallback
        $ldap_tag = CAppUI::conf('admin LDAP ldap_tag');
        if (!$ldap_tag) {
            return null;
        }

        $id400 = $this->loadLastId400($ldap_tag);
        if ($id400 && $id400->_id) {
            return $id400->id400;
        }

        return null;
    }

    /**
     * @param string $ldap_uid
     *
     * @return static|null
     * @throws Exception
     */
    public static function loadFromLdapUid(string $ldap_uid): ?self
    {
        if (!$ldap_uid) {
            return null;
        }

        $self = new self();

        if ($self->isFieldInstalled('ldap_uid')) {
            $self->ldap_uid = $ldap_uid;
            $self->loadMatchingObjectEsc();

            if ($self && $self->_id) {
                return $self;
            }

            return null;
        }

        $ldap_tag = CAppUI::conf('admin LDAP ldap_tag');

        if (!$ldap_tag) {
            return null;
        }

        // Idex fallback
        $idex = CIdSante400::getMatch('CUser', $ldap_tag, $ldap_uid);

        if ($idex && $idex->_id) {
            /** @var CUser $user */
            $user = $idex->loadTargetObject();

            if ($user && $user->_id) {
                return $user;
            }
        }

        return null;
    }

    public static function findFromFCSub(string $sub): ?CUser
    {
        if (!$sub) {
            return null;
        }

        $user         = new self();
        $user->sub_fc = $sub;
        $user->loadMatchingObjectEsc();

        if ($user && $user->_id) {
            return $user;
        }

        return null;
    }

    /**
     * @param IncrementLoginAttemptsBadge|null $increment
     *
     * @return void
     * @throws Exception
     */
    public function tryIncrementLoginAttempts(?IncrementLoginAttemptsBadge $increment): void
    {
        if ($increment && $increment->isEnabled() && $this->loginErrorsReady()) {
            // If the user exists, but has given a wrong password let's increment his error count
            $this->user_login_errors++;

            // Password is INVALID, user is locked (by ATTEMPTS),
            // but lock datetime is EXPIRED so we set lock datetime to now
            if ($this->isLockedByAttempts() && !$this->isStillLockedByDatetime()) {
                $this->setLockDatetime();
            }

            $this->user_login_errors;
            $this->store();
        }
    }
}
