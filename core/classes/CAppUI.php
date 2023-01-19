<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use League\OAuth2\Client\Token\AccessToken;
use Ox\Core\Auth\Badges\WeakPasswordBadge;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Module\CModule;
use Ox\Core\Mutex\CMbFileMutex;
use Ox\Core\Network\IpRangeMatcher;
use Ox\Core\OAuth2\OIDC\FC\Client as FcClient;
use Ox\Core\OAuth2\OIDC\PSC\Client as PscClient;
use Ox\Core\OAuth2\OIDC\TokenSet;
use Ox\Mediboard\Admin\CKerberosLdapIdentifier;
use Ox\Mediboard\Admin\CLDAP;
use Ox\Mediboard\Admin\CLDAPNoSourceAvailableException;
use Ox\Mediboard\Admin\CMbInvalidCredentialsException;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Medimail\CMedimailAccount;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMessageDest;
use Ox\Mediboard\MondialSante\CMondialSanteAccount;
use Ox\Mediboard\Mssante\CMSSanteUserAccount;
use Ox\Mediboard\System\CConfiguration;
use Ox\Mediboard\System\ConfigurationManager;
use Ox\Mediboard\System\CPreferences;
use Ox\Mediboard\System\CSourcePOP;
use Ox\Mediboard\System\CTranslationOverwrite;
use Ox\Mediboard\System\CUserAgent;
use Ox\Mediboard\System\CUserAuthentication;
use Ox\Mediboard\System\CUserAuthenticationError;
use Throwable;
use TypeError;

/**
 * The Application UI weird Class
 * (Target) Responsibilities:
 *  - logging
 *  - messaging
 *  - localization
 *  - user preferences
 *  - system configuration
 *
 * @todo Is being split into CApp et CUI classes
 */
class CAppUI
{
    const LOCALES_PREFIX          = ".__prefixes__"; // Prefixed by "." to be first in the list
    const LOCALES_OVERWRITE       = "__overwrite__"; // Prefixed by "." to be first in the list
    const PASSWORD_REMAINING_DAYS = 'password_remaining_days';

    const MESSAGERIE_ACCOUNTS_CACHE = 'CAppUI.getMessagerieAccounts';

    const MEDIBOARD_EXT_THEME = "mediboard_ext";

    public const UI_MSG_OK      = 1;
    public const UI_MSG_ALERT   = 2;
    public const UI_MSG_WARNING = 3;
    public const UI_MSG_ERROR   = 4;

    private const MULTI_TAB_MSG_READ = 'multi-tab-msg-read';

    /** @var CAppUI */
    static $instance;

    /** @var CMediusers Connected user */
    static $user;

    /** @var bool Mobile flag => use mobile folder */
    static $mobile = false;

    /** @var bool Dialog mode */
    static $dialog;

    /** @var bool Do login */
    static $do_login;

    /** @var bool Tells if we must refresh session */
    static $session_no_revive = false;

    /** @var bool Is it a token based session ? */
    static $token_session = false;

    /** @var int Token expiration ISO datetime */
    static $token_expiration;

    /** @var bool Token restricted: session will be closed right after page display */
    static $token_restricted;

    /** @var integer Session CViewAccessToken ID */
    static $token_id;

    /** @var AccessToken */
    static $oauth2_token;

    /** @var CUserAuthenticationSuccess Authentication information */
    static $auth_info;

    static $renew_ldap_pwd;

    /** @var int Module access level */
    static $access_level;

    /* --- <Localization> --- */
    /** @var bool Localization skipped if false */
    static $localize = true;

    /** @var string Current language */
    static $lang;

    /** @var string Language alert mask */
    static $locale_mask = "";

    /** @var string[] List of unlocalized strings */
    static $unlocalized = [];

    /** @var string Mask of strings to ignore for the localization warning */
    static $localize_ignore = '/(^CExObject|^CMbObject\.dummy)/';

    /** @var array[] Array of locales, groupes by prefix */
    static $locales = [];

    /** @var bool[] List of flags indicating loaded locales groups */
    static protected $locales_loaded = [];

    /** @var bool Echo inline stepMessage & stepAjax */
    private static $echo_step = true;

    /** @var array */
    public static $locale_info = [];

    /* --- </Localization> --- */

    /* -- SESSION VARS BELOW -- */
    public $user_id = 0;
    public $_is_intranet;
    public $ip;
    public $proxy;

    // DEPRECATED Use CAppUI::$user instead
    // @todo Remove all calls to these variables
    public $user_first_name;
    public $user_last_name;
    public $user_email;

    // Do not remove this, used in order to identify a CPatientUser connection (CUser without a CMediusers)
    public $user_type;

    public $user_group;
    public $user_prev_login;
    public $user_last_login;
    public $user_remote;

    // @todo Remove many calls in templates
    // @todo Handle the CMediusers::get() and CUser::get() cases
    /** @var CMediusers */
    public $_ref_user;
    // END DEPRECATED

    /** @var bool Weak password */
    public $weak_password;

    /** @var bool Touch device */
    public $touch_device = false;

    // Global collections
    public $messages   = [];
    public $user_prefs = [];
    public $update_hash;

    /** @var string Default page for a redirect call */
    public $defaultRedirect = "";

    /** @var string Session name */
    public $session_name = "";

    /** @var string Authentication method used */
    public $auth_method;

    /** @var CUserAgent User agent information */
    public $ua;

    public $_renew_ldap_pwd;

    /** @var bool Is the connected user LDAP related? */
    public $_is_ldap_linked;

    /** @var CUserAuthentication $_ref_last_auth */
    public $_ref_last_auth;

    /** @var TokenSet|null */
    public $oidc_tokens;

    /**
     * @var string Used to auto-mapping mediuser
     */
    public $_psc_rpps_attempt_mapping;

    /** @var string */
    public $_fc_mapping;

    /**
     * Init by front ctrl
     *
     * @return void
     */
    static function init()
    {
        // Message No Constants
        define("UI_MSG_OK", 1);
        define("UI_MSG_ALERT", 2);
        define("UI_MSG_WARNING", 3);
        define("UI_MSG_ERROR", 4);

        // choose to alert for missing translation or not
        $locale_alert        = CAppUI::conf("locale_alert");
        CAppUI::$locale_mask = CAppUI::conf("locale_warn") ? "$locale_alert%s$locale_alert" : null;
    }

    /**
     * Initializes the CAppUI singleton
     *
     * @return CAppUI The singleton
     */
    static function initInstance()
    {
        return self::$instance = new CAppUI();
    }

    /**
     * Executed prior to any serialization of the object
     *
     * @return array Array of field names to be serialized
     */
    function __sleep()
    {
        $vars = get_object_vars($this);
        unset($vars["_ref_user"]);

        return array_keys($vars);
    }


    /**
     * Used to include a php file from the module directory
     *
     * @param string $name [optional] The module name
     * @param string $file [optional] The name of the file to include
     *
     * @return mixed Job-done bool or file return value
     * @todo Migrate to CApp
     *
     */
    static function requireModuleFile($name = null, $file = null)
    {
        if ($name && $root = self::conf("root_dir")) {
            $filename = $file ? $file : $name;

            // dP prefix hack
            if (!is_dir(__DIR__ . "/../../modules/$name") && strpos($name, "dP") !== 0) {
                $name = "dP$name";
            }

            return include_once "$root/modules/$name/$filename.php";
        }

        return false;
    }

    /**
     * Used to store information in tmp directory
     *
     * @param string $subpath in tmp directory
     *
     * @return string The path to the include file
     * @todo Migrate to CApp
     *
     */
    static function getTmpPath($subpath)
    {
        if ($subpath && $root = self::conf("root_dir")) {
            return "$root/tmp/$subpath";
        }

        return false;
    }

    /**
     * Find directories in a root subpath, excluding source control files
     *
     * @param string $subpath The subpath to read
     *
     * @return array A named array of the directories (the key and value are identical)
     */
    static function readDirs($subpath)
    {
        $root_dir = self::conf("root_dir");
        $dirs     = [];
        $d        = dir("$root_dir/$subpath");

        while (false !== ($name = $d->read())) {
            if (
                $name !== "."
                && $name !== ".."
                && $name !== "CVS"
                && is_dir("$root_dir/$subpath/$name")
            ) {
                $dirs[$name] = $name;
            }
        }

        $d->close();

        return $dirs;
    }

    /**
     * Find files in a roo subpath, excluding a specific filter
     *
     * @param string $subpath The path to read
     * @param string $filter  Filter as a regular expression
     *
     * @return array A named array of the files (the key and value are identical)
     */
    static function readFiles($subpath, $filter = ".")
    {
        $files = [];

        if ($handle = opendir($subpath)) {
            while (false !== ($file = readdir($handle))) {
                if ($file !== "."
                    && $file !== ".."
                    && preg_match("/$filter/", $file)
                ) {
                    $files[$file] = $file;
                }
            }
            closedir($handle);
        }

        return $files;
    }

    /**
     * Utility function to check whether a file name is "safe"
     * Prevents from access to relative directories (eg ../../deadlyfile.php)
     *
     * @param string $file The file name
     *
     * @return string Sanitized file name
     */
    static function checkFileName($file)
    {
        // define bad characters and their replacement
        $bad_chars   = ";.\\";
        $bad_replace = "..."; // Needs the same number of chars as $bad_chars

        // check whether the filename contained bad characters
        if (strpos(strtr($file ?? '', $bad_chars, $bad_replace), ".") !== false) {
            self::accessDenied();
        }

        return $file;
    }

    /**
     * Get current user agent
     *
     * @return CUserAgent
     */
    static function getUA()
    {
        if (!CAppUI::$instance->ua) {
            CAppUI::$instance->ua = CUserAgent::create(false);
        }

        return CAppUI::$instance->ua;
    }

    /**
     * Redirects the browser to a new page.
     *
     * @param string $params    HTTP GET paramaters to apply
     * @param int    $http_code HTTP code for the redirection
     *
     * @return void
     */
    static function redirect($params = "", $http_code = 302)
    {
        if (CValue::get("dontRedirect")) {
            return;
        }

        if (CValue::get("dialog")) {
            $params .= "&dialog=1";
        }

        if (CValue::get("ajax")) {
            $params .= "&ajax=1";
        }

        if (CValue::get("suppressHeaders")) {
            $params .= "&suppressHeaders=1";
        }

        $query = ($params && $params[0] !== "#" ? "?$params" : "");

        header("Location: index.php$query", true, $http_code);
        CApp::rip(false);
    }

    /**
     * Returns the CSS class corresponding to a message type
     *
     * @param int $type Message type as a UI constant
     *
     * @return string The CSS class
     */
    static function getErrorClass($type = UI_MSG_OK)
    {
        switch ($type) {
            case UI_MSG_ERROR:
                return "error";

            case UI_MSG_WARNING:
                return "warning";

            default:
            case UI_MSG_OK:
            case UI_MSG_ALERT:
                return "info";
        }
    }

    /**
     * Add message to the the system UI
     *
     * @param string $msg  The internationalized message
     * @param int    $type [optional] Message type as a UI constant
     * @param mixed  $_    [optional] Any number of printf-like parameters to be applied
     *
     * @return void
     * @todo rename to addMsg()
     *
     */
    static function setMsg($msg, $type = UI_MSG_OK, $_ = null)
    {
        $args = func_get_args();
        $msg  = CAppUI::tr($msg, array_slice($args, 2));

        if (!isset(self::$instance->messages[$type][$msg])) {
            self::$instance->messages[$type][$msg] = 0;
        }

        self::$instance->messages[$type][$msg]++;
    }

    /**
     * Add message to the the system UI from Ajax call
     *
     * @param string $msg  The internationalized message
     * @param int    $type [optional] Message type as a UI constant
     * @param mixed  $_    [optional] Any number of printf-like parameters to be applied
     *
     * @return void
     * @todo rename to addAjaxMsg()
     *
     */
    static function displayAjaxMsg($msg, $type = UI_MSG_OK, $_ = null)
    {
        $args  = func_get_args();
        $msg   = CAppUI::tr($msg, array_slice($args, 2));
        $msg   = CMbString::htmlEntities($msg);
        $class = self::getErrorClass($type);
        self::callbackAjax('$("systemMsg").show().insert', "<div class='$class'>$msg</div>");
    }

    /**
     * Check whether UI has any problem message
     *
     * @return bool True if no alert/warning/error message
     */
    static function isMsgOK()
    {
        $messages = self::$instance->messages;
        $errors   =
            (is_countable(@$messages[UI_MSG_ALERT]) ? count(@$messages[UI_MSG_ALERT]) : 0) +
            (is_countable(@$messages[UI_MSG_ALERT]) ? count(@$messages[UI_MSG_WARNING]) : 0) +
            (is_countable(@$messages[UI_MSG_ERROR]) ? count(@$messages[UI_MSG_ERROR]) : 0);

        return $errors === 0;
    }

    /**
     * Add a action pair message
     * Make an error is message is not null, ok otherwise
     *
     * @param string $msg    The internationalized message
     * @param string $action The internationalized action
     * @param mixed  $_      [optional] Any number of printf-like parameters to be applied to action
     *
     * @return void
     * @todo rename to addActionMsg()
     *
     */
    static function displayMsg($msg, $action, $_ = null)
    {
        $args   = func_get_args();
        $action = self::tr($action, array_slice($args, 2));
        if ($msg) {
            $msg = self::tr($msg);
            // @todo Should probably not translate once again
            self::setMsg("$action: $msg", UI_MSG_ERROR);

            return;
        }

        self::setMsg($action, UI_MSG_OK);
    }

    /**
     * Render HTML system message bloc corresponding to current messages
     * Possibly clear messages, thus being shown only once
     *
     * @param boolean $reset [optional] Clear messages if true
     *
     * @return string HTML divs
     */
    static function getMsg($reset = true)
    {
        $return = "";

        if (self::$instance && self::$instance->messages) {
            ksort(self::$instance->messages);

            foreach (self::$instance->messages as $type => $messages) {
                $class = self::getErrorClass($type);

                foreach ($messages as $message => $count) {
                    $render = $count > 1 ? "$message x $count" : $message;
                    $return .= "<div class='$class'>$render</div>";
                }
            }

            if ($reset) {
                self::$instance->messages = [];
            }
        }

        return CMbString::purifyHTML($return);
    }

    /**
     * Display an AJAX message step after translation
     *
     * @param int    $type Message type as a UI constant
     * @param string $msg  The internationalized message
     * @param mixed  $_    [optional] Any number of printf-like parameters to be applied
     *
     * @return void
     * @todo Rename to ajaxNotice()
     *
     */
    static function stepMessage($type, $msg, $_ = null)
    {
        $args = func_get_args();
        $msg  = CAppUI::tr($msg, array_slice($args, 2));
        $msg  = CMbString::purifyHTML($msg);

        $class = self::getErrorClass($type);
        if (static::$echo_step) {
            echo "\n<div class='small-$class'>$msg</div>";
        } else {
            CApp::log($msg);
        }
    }

    /**
     * Display an AJAX step, and exit on error messages
     *
     * @param string $msg  The internationalized message
     * @param int    $type [optional] Message type as a UI constant
     * @param mixed  $_    [optional] Any number of printf-like parameters to be
     *
     * @return void
     * @todo Rename to ajaxNsg()
     *
     * @todo Switch parameter order, like stepMessage()
     */
    static function stepAjax($msg, $type = UI_MSG_OK, $_ = null)
    {
        $args = func_get_args();
        $msg  = CAppUI::tr($msg, array_slice($args, 2));
        $msg  = CMbString::purifyHTML($msg);

        $class = self::getErrorClass($type);
        if (static::$echo_step) {
            echo "\n<div class='$class'>$msg</div>";
        } else {
            CApp::log($msg);
        }

        if ($type == UI_MSG_ERROR) {
            CApp::rip();
        }
    }

    public static function turnOffEchoStep()
    {
        static::$echo_step = false;
    }

    public static function turnOnEchoStep()
    {
        static::$echo_step = true;
    }

    /**
     * Display a common error, without details
     *
     * @param string $msg The message
     *
     * @return void
     */
    static function commonError($msg = 'common-error-An error occurred')
    {
        CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }

    /**
     * Echo an ajax callback with given value
     *
     * @param string $callback Name of the javascript function
     * @param string $args     Value parameter(s) for javascript function
     *
     * @return void
     */
    static function callbackAjax($callback, $args = '')
    {
        $args = func_get_args();
        $args = array_slice($args, 1);

        $args = array_map([CMbArray::class, "toJSON"], $args);
        $args = implode(",", $args);

        self::js("try { $callback($args); } catch(e) { console.error(e) }");
    }

    /**
     * Echo an HTML javascript block
     *
     * @param string $script Javascript code
     *
     * @return void
     */
    static function js($script)
    {
        echo "\n<script>$script</script>";
    }

    /**
     * Authentication failure message
     *
     * @param string $msg Message to display
     *
     * @return bool
     */
    private static function authFailed($msg, $login = null, $user_id = null, $auth_method = null)
    {
        $args = func_get_args();
        array_shift($args); // $msg
        array_shift($args); // $login
        array_shift($args); // $user_id
        array_shift($args); // $auth_method
        array_unshift($args, $msg, UI_MSG_ERROR);

        call_user_func_array([CAppUI::class, "setMsg"], $args);

        if ($login) {
            CUserAuthenticationError::logError($login, $user_id, $auth_method);
        }

        header("HTTP/1.1 401 Unauthorized", true, 401);

        return false;
    }

    /**
     * Login function, handling standard login, loginas, LDAP connection
     * Preferences get loaded on success
     *
     * @param bool $force_login To allow admin users to login as someone else
     *
     * @return bool
     * @throws CMbModelNotFoundException
     */
    public static function login(bool $force_login = false): bool
    {
        $ldap_connection     = CAppUI::conf("admin LDAP ldap_connection");
        $allow_login_as_ldap = CAppUI::conf("admin LDAP allow_login_as_admin");

        // Login as
        $loginas    = trim(CValue::request("loginas"));
        $passwordas = trim(CValue::request("passwordas"));

        // LDAP
        $ldap_guid = trim(CValue::get("ldap_guid"));

        // Standard login
        $username = trim(CValue::request("username"));
        $password = trim(CValue::request("password"));

        // Token sign-in
        $token_hash = trim(CValue::request("token"));

        if ($possibly_token_hash = CViewAccessToken::getShortURLTokenHash()) {
            $token_hash = $possibly_token_hash;
        }

        // PSC
        $is_psc_login           = trim(CValue::request("psc"));
        $mediuser_update_fields = [];

        // FC
        $is_fc_login = trim(CValue::request('fc'));

        // OIDC
        $code  = trim(CValue::request('code'));
        $state = trim(CValue::request('state'));
        $nonce = trim(CValue::request('nonce'));

        // CPS login
        $signature            = CValue::request("signature");
        $certificat_signature = CValue::request("certificat_signature");

        $krb_username  = ($_SERVER['REMOTE_USER']) ?? null;
        $krb_auth_type = ($_SERVER['AUTH_TYPE']) ?? null;

        // Test login and password validity
        $user              = new CUser();
        $user->_is_logging = true;

        $previous_user_id = null;
        $is_intranet      = self::isIntranet();

        // -------------- Login as: no need to provide a password for administrators
        if ($loginas) {
            static::$instance->auth_method = CUserAuthentication::AUTH_METHOD_SUBSTITUTION;

            if ((static::$instance->user_type != 1) && !$force_login) {
                return static::authFailed(
                    "Auth-failed-loginas-admin",
                    $loginas,
                    static::$instance->user_id,
                    static::$instance->auth_method
                );
            }

            $username = $loginas;
            $password = ($ldap_connection ? $passwordas : null);

            if ((static::$instance->user_type == 1) && $allow_login_as_ldap) {
                $password = null;
            }

            $user->user_username  = $username;
            $user->_user_password = $password;

            $previous_user_id = CUser::get()->_id;
        } elseif ($ldap_connection && $ldap_guid) {
            // -------------- LDAP sign-in
            static::$instance->auth_method = CUserAuthentication::AUTH_METHOD_LDAP_GUID;

            try {
                $user = CUser::loadFromLdapUid($ldap_guid);

                if (!$user || !$user->_id) {
                    throw new Exception();
                }

                CAppUI::$instance->auth_method = CUserAuthentication::AUTH_METHOD_LDAP_GUID;
            } catch (Exception $e) {
                return static::authFailed(
                    "Auth-failed-combination",
                    $ldap_guid,
                    null,
                    static::$instance->auth_method
                );
            }
        } elseif ($token_hash) {
            // -------------- Token sign-in
            static::$instance->auth_method = CUserAuthentication::AUTH_METHOD_TOKEN;

            $token = CViewAccessToken::getByHash($token_hash);

            if (!$token->isValid()) {
                $token_user_id = ($token && $token->user_id) ? $token->user_id : null;

                return static::authFailed("Auth-failed-invalidToken", $token_user_id, static::$instance->auth_method);
            }

            $token->useIt();
            $token->applyParams();

            $user->load($token->user_id);
        } elseif (static::$auth_info && static::$auth_info->user_id) {
            // Todo: Remove
            // -------------- Authentication handler
            $auth                          = static::$auth_info;
            static::$instance->auth_method = $auth->auth_method;

            $user->load($auth->user_id);
        } elseif ($signature && $certificat_signature) {
            // -------------- CPS login
            static::$instance->auth_method = CUserAuthentication::AUTH_METHOD_CARD;

            CDocumentItem::$check_send_problem = false;

            $file               = new CFile();
            $file->file_name    = 'certificat_signature.crt';
            $file->object_class = 'CMediusers';
            $file->private      = 1;
            $files              = $file->loadMatchingList();

            $md5_certificat = md5($certificat_signature);
            $user_id        = null;

            /** @var CFile $_file */
            foreach ($files as $_file) {
                if ($md5_certificat === md5(file_get_contents($_file->_file_path))) {
                    $user_id = $_file->object_id;
                    break;
                }
            }

            if (!$user_id) {
                return static::authFailed(
                    'CUserAuthentication-error-Authentication card mismatch',
                    $user->user_username,
                    null,
                    static::$instance->auth_method
                );
            }

            $file->object_id = $user_id;
            $file->file_name = 'certificat_auth.crt';
            $file->loadMatchingObject();

            CDocumentItem::$check_send_problem = false;

            if (!$file->_id) {
                return static::authFailed(
                    'CUserAuthentication-error-No configured authentication card',
                    $user->user_username,
                    $user->_id,
                    static::$instance->auth_method
                );
            }

            $cipher = CMbSecurity::getCipher(CMbSecurity::RSA);

            if (
                CMbSecurity::verify(
                    $cipher,
                    CMbSecurity::SHA1,
                    2,
                    $certificat_signature,
                    'mediboard',
                    base64_decode($signature)
                )
            ) {
                $user->load($user_id);
            }
        } elseif (
            ($krb_username && ($krb_auth_type === 'Negotiate') && CKerberosLdapIdentifier::isReady())
            && !($username && $password)
        ) {
            // -------------- Kerberos login
            static::$instance->auth_method = CUserAuthentication::AUTH_METHOD_SSO;

            if ($krb_user = CKerberosLdapIdentifier::findUserByName($krb_username)) {
                $user = $krb_user;
            } else {
                return static::authFailed(
                    'CKerberosLdapIdentifier-error-User not found',
                    $krb_username,
                    null,
                    static::$instance->auth_method,
                    $krb_username
                );
            }
        } elseif ($is_psc_login && static::isLoginPSCEnabled() && CModule::getActive('mediusers')) {
            // -------------- Pro Sante Connect
            static::$instance->auth_method = CUserAuthentication::AUTH_METHOD_SSO;

            // Load provider
            $psc_provider = new PscClient();

            try {
                if (!$code && !$state) {
                    $endpoint = $psc_provider->requestAuthorization();

                    header("Location: {$endpoint}");
                    CApp::rip();
                }

                $psc_provider->requestTokens($code, $state);
                $info = $psc_provider->getUserInfo();

                if ($info->getId() === null) {
                    // Todo: Obfuscate error message?
                    return static::authFailed('ProSanteConnect-error-User not found');
                }

                /** @var CMediusers $psc_matching_mediuser */
                $psc_matching_mediuser = CMediusers::loadFromRPPS($info->getRppsOrAdeli(), true);

                // Mediuser match with the rpps
                if ($psc_matching_mediuser->_id) {
                    // Technical identifier (sub)
                    if ($psc_matching_mediuser->sub_psc === null) {
                        $mediuser_update_fields['sub_psc'] = $info->getSub();
                    } elseif ($psc_matching_mediuser->sub_psc !== $info->getSub()) {
                        // Todo: Obfuscate error message?
                        return static::authFailed('ProSanteConnect-error-User not mapping');
                    }

                    // Assign matching user
                    $user = $psc_matching_mediuser->loadRefUser();

                    // Force redirection (clean uri)
                    static::setRedirectFromUserPref($user);

                    // Set sso information
                    static::$instance->oidc_tokens = $psc_provider->getTokenSet();
                } elseif ($info->isIdRpps() && CAppUI::conf('admin ProSanteConnect enable_automapping')) {
                    $session_id = session_id();
                    $cache      = Cache::getCache(Cache::OUTER);

                    $cache->set("pcs-rpps-{$session_id}", $info->getRppsOrAdeli(), 120);
                    $cache->set("pcs-sub-{$session_id}", $info->getSub(), 120);

                    // Keep Sub to auto-mapping
                    static::$instance->_psc_rpps_attempt_mapping = '1';

                    return static::authFailed(
                    // Todo: Obfuscate error message?
                        'ProSanteConnect-error-User not mapping',
                        null,
                        null,
                        static::$instance->auth_method,
                        $info->getName()
                    );
                } else {
                    CApp::log('PSC : no matching mediuser for idNat ' . $info->getId());

                    // Todo: Obfuscate error message ?
                    return static::authFailed('ProSanteConnect-error-User not found');
                }
            } catch (Throwable $t) {
                // Failed to get the access token or user details.
                // Todo: Obfuscate error message ?
                return static::authFailed('ProSanteConnect-error-service');
            }
        } elseif ($is_fc_login && self::isLoginFCEnabled() && CModule::getActive('mediusers')) {
            // -------------- Pro Sante Connect
            static::$instance->auth_method = CUserAuthentication::AUTH_METHOD_SSO;

            // Load provider
            $fc_provider = new FcClient();

            try {
                if (!$code && !$state && !$nonce) {
                    $endpoint = $fc_provider->requestAuthorization();

                    header("Location: {$endpoint}");
                    CApp::rip();
                }

                $fc_provider->requestTokens($code, $state, $nonce);
                $info = $fc_provider->getUserInfo();

                if ($info->getSub() === null) {
                    // Todo: Obfuscate error message?
                    return static::authFailed('FranceConnect-error-User not found');
                }

                $fc_mapping_user = CUser::findFromFCSub($info->getSub());

                // User match with the sub
                if ($fc_mapping_user->_id) {
                    // Assign matching user
                    $user = $fc_mapping_user;

                    // Force redirection (clean uri)
                    static::setRedirectFromUserPref($user);

                    // Set sso information
                    static::$instance->oidc_tokens = $fc_provider->getTokenSet();
                } else {
                    $session_id = session_id();
                    $cache      = Cache::getCache(Cache::OUTER);

                    $cache->set("fc-{$session_id}", $info->getSub(), 120);

                    // Keep Sub to auto-mapping
                    static::$instance->_fc_mapping = '1';

                    return static::authFailed(
                    // Todo: Obfuscate error message?
                        'FranceConnect-error-User not mapping',
                        null,
                        null,
                        static::$instance->auth_method,
                        $info->getName()
                    );
                }
            } catch (Throwable $t) {
                // Failed to get the access token or user details.
                // Todo: Obfuscate error message ?
                return static::authFailed('FranceConnect-error-service');
            }
        } else {
            // -------------- Standard sign-in
            static::$instance->auth_method = CUserAuthentication::AUTH_METHOD_STANDARD;

            if (!$username) {
                return static::authFailed("Auth-failed-nousername", null, null, static::$instance->auth_method);
            }

            if (!$password) {
                return static::authFailed("Auth-failed-nopassword", $username, null, static::$instance->auth_method);
            }

            $user->user_username  = $username;
            $user->_user_password = $password;
        }

        // Perform the password check (by loadMatching)
        if (!$user->_id) {
            $user->preparePassword();
            $user->loadMatchingObject();
        }

        // User template case
        if ($user->template) {
            return static::authFailed(
                "Auth-failed-combination",
                $user->user_username,
                $user->_id,
                static::$instance->auth_method
            );
        }

        // User is a secondary user (user duplicate)
        if ($user->isSecondary()) {
            return static::authFailed(
                'Auth-failed-combination',
                $user->user_username,
                $user->_id,
                static::$instance->auth_method
            );
        }

        // LDAP case (when not using a ldap_guid), we check is the user in the LDAP directory is still allowed
        // TODO we shoud check it when using ldap_guid too
        if ($ldap_connection && $username) {
            // Forcing weak password spec for LDAP users
            CAppUI::$instance->weak_password = false;

            $user_ldap                = new CUser();
            $user_ldap->user_username = $username;
            $user_ldap->loadMatchingObject();

            $ldap_uid = $user_ldap->getLdapUid();

            // The user in linked to the LDAP
            if ($ldap_uid) {
                $user_ldap->_user_password = $password;
                $user_ldap->_bound         = false;

                try {
                    $user = CLDAP::login($user_ldap, $ldap_uid);

                    if (!$user->_bound) {
                        return static::authFailed(
                            "Auth-failed-combination",
                            $username,
                            $user_ldap->_id,
                            static::$instance->auth_method
                        );
                    }
                } catch (CLDAPNoSourceAvailableException $e) {
                    // No LDAP source, fallback to basic auth
                } // InvalidCredentials
                catch (CMbInvalidCredentialsException $e) {
                    // No login attempts blocking if user is LDAP-bound
                    if ($user_ldap->loginErrorsReady()) {
                        static::authFailed(
                        /*$e->getMessage()*/ "Auth-failed-combination",
                                             $username,
                                             $user_ldap->_id,
                                             static::$instance->auth_method
                        );
                    }

                    return false;
                } // Maybe source unreachable ?
                catch (CMbException $e) {
                    // No UI_MSG_ERROR nor $e->stepAjax as it needs to run through!
                    //static::setMsg($e->getMessage(), UI_MSG_WARNING);
                }
            }
        }

        // Set in CLDAP::login
        if ($user->_ldap_expired) {
            CUser::setPasswordMustChange();
            static::$instance->_renew_ldap_pwd = true;
        }

        if (!static::checkPasswordAttempt($user)) {
            return false;
        }

        $user->resetLoginErrorsCounter(true);

        // Put user_group in AppUI
        static::$instance->user_remote = 1;

        $ds = CSQLDataSource::get("std");

        // We get the user's group if the Mediusers module is installed
        if ($ds->hasTable("users_mediboard") && $ds->hasTable("groups_mediboard")) {
            $sql = "SELECT `remote` FROM `users_mediboard` WHERE `user_id` = '$user->_id'";

            static::$instance->user_remote = $ds->loadResult($sql);

            $sql = "SELECT `groups_mediboard`.`group_id`
        FROM `groups_mediboard`, `functions_mediboard`, `users_mediboard`
        WHERE `groups_mediboard`.`group_id` = `functions_mediboard`.`group_id`
        AND `functions_mediboard`.`function_id` = `users_mediboard`.`function_id`
        AND `users_mediboard`.`user_id` = '$user->_id'";

            // Keep sure that is realised before CUserAuthentication::logAuth() (needed for strong authentication SMS)
            static::$instance->user_group = $ds->loadResult($sql);
        }

        // Test if remote connection is allowed
        if (!$is_intranet && static::$instance->user_remote == 1 && $user->user_type != 1) {
            return static::authFailed(
                "Auth-failed-user-noremoteaccess",
                $user->user_username,
                $user->_id,
                CAppUI::$instance->auth_method
            );
        }

        $whitelist = CAppUI::gconf('system network ip_address_range_whitelist', CAppUI::$instance->user_group);

        // A whitelist exists, user is not a robot and not an ADMIN.
        if ($whitelist && !$user->isRobot() && $user->user_type != 1) {
            $whitelist = explode("\n", $whitelist);

            try {
                $ip_range_checker = new IpRangeMatcher($whitelist);

                if (!$ip_range_checker->matches(CAppUI::$instance->ip)) {
                    throw new Exception();
                }
            } catch (Throwable $t) {
                return static::authFailed(
                    'Auth-failed-Authentication is not allowed from your location.',
                    $user->user_username,
                    $user->_id,
                    CAppUI::$instance->auth_method
                );
            }
        }

        static::$instance->user_id = $user->_id;

        static::$instance->weak_password = $user->checkPasswordWeakness();

        // save the last_login dateTime
        $auth = CUserAuthentication::logAuth($user, $previous_user_id);

        static::$instance->ua             = $auth ? $auth->_ref_user_agent : new CUserAgent();
        static::$instance->_ref_last_auth = $auth;

        // <DEPRECATED>
        static::$instance->user_first_name = $user->user_first_name;
        static::$instance->user_last_name  = $user->user_last_name;
        static::$instance->user_email      = $user->user_email;
        static::$instance->user_type       = $user->user_type;
        static::$instance->user_last_login = $user->getLastLogin();
        // </DEPRECATED>

        // load the user preferences
        static::buildPrefs();

        // Event probability : inactiver expired users
        $denominator = CAppUI::conf("admin CUser probability_force_inactive_old_authentification");
        CApp::doProbably(
            $denominator,
            function () {
                CUserAuthentication::inactiverExpiredUsers();
            }
        );

        if ($auth) {
            // Todo: Cannot use handlers because of CConfiguration not already built
            $disconnect_pref = CPreferences::getPref('admin_unique_session', $auth->user_id);

            if ($disconnect_pref && $disconnect_pref['used']) {
                $auth->disconnectSimilarOnes();
            }
        }

        // Pro Sante Connect
        if (CModule::getActive('mediusers')) {
            // Auto mapping (fi + fs login)
            $psc_mapping = (bool)trim(CValue::request("psc_mapping"));

            if ($psc_mapping || isset($mediuser_update_fields['sub_psc'])) {
                $rpps = null;
                $sub  = ($mediuser_update_fields['sub_psc']) ?? null;

                if ($sub === null) {
                    $session_id = session_id();
                    $cache      = Cache::getCache(Cache::OUTER);

                    $rpps = $cache->get("pcs-rpps-{$session_id}");
                    $sub  = $cache->get("pcs-sub-{$session_id}");

                    $cache->delete("pcs-rpps-{$session_id}");
                    $cache->delete("pcs-sub-{$session_id}");
                }

                if ($rpps || $sub) {
                    // get mediuser (without user ref)
                    CMediusers::$user_autoload = false;
                    $mediuser                  = CMediusers::findOrFail($user->_id);
                    CMediusers::$user_autoload = true;

                    if ($rpps) {
                        $mediuser->rpps = $rpps;
                    }

                    if ($sub) {
                        $mediuser->sub_psc = $sub;
                    }

                    $mediuser->store();

                    static::setRedirectFromUserPref($user);
                }
            }
        }

        if ((bool)CValue::request('fc_mapping')) {
            $session_id = session_id();
            $cache      = Cache::getCache(Cache::OUTER);

            if ($sub = $cache->get("fc-{$session_id}")) {
                $user->sub_fc = $sub;
                $user->store();

                $cache->delete("fc-{$session_id}");

                static::setRedirectFromUserPref($user);
            }
        }

        return true;
    }

    /**
     * Todo: Check if still necessary after v2 auth merging and/or move to CUser
     *
     * Todo: Do not really perform a password check...
     *
     * Check wether login/password is found
     * Handle password attempts count
     *
     * @param CUser $user User whose password attempt to check
     *
     * @return bool True is attempt is successful
     */
    static function checkPasswordAttempt(CUser $user)
    {
        $sibling                = new CUser();
        $sibling->user_username = $user->user_username;
        $sibling->loadMatchingObject();
        $sibling->loadRefMediuser();

        $mediuser = $sibling->_ref_mediuser;

        if ($mediuser && $mediuser->_id) {
            if (!$mediuser->actif) {
                return static::authFailed(
                    "Auth-failed-combination",
                    $sibling->user_username,
                    $sibling->_id,
                    static::$instance->auth_method
                );
            }

            $today = CMbDT::date();
            $deb   = $mediuser->deb_activite;
            $fin   = $mediuser->fin_activite;

            // Check if the user is in his activity period
            if (($deb && $deb > $today) || ($fin && $fin <= $today)) {
                return static::authFailed(
                    "Auth-failed-combination",
                    $sibling->user_username,
                    $sibling->_id,
                    static::$instance->auth_method
                );
            }
        }

        // Password is INVALID, user is locked (by ATTEMPTS), but lock datetime is EXPIRED so we set lock datetime to now
        if (!$user->_id && $sibling->isLockedByAttempts() && !$sibling->isStillLockedByDatetime()) {
            $sibling->setLockDatetime();
        }

        // User is locked
        if ($sibling->isLocked()) {
            // User typed a valid password, we inform that account is locked
            if ($user->_id) {
                return static::authFailed(
                    "Auth-failed-user-locked",
                    $sibling->user_username,
                    $sibling->_id,
                    static::$instance->auth_method
                );
            }

            // Invalid password, not showing more information
            return static::authFailed(
                "Auth-failed-combination",
                $sibling->user_username,
                $sibling->_id,
                static::$instance->auth_method
            );
        }

        // Wrong login and/or password
        if (!$user->_id) {
            // If the user exists, but has given a wrong password let's increment his error count
            if ($user->loginErrorsReady() && $sibling->_id) {
                if (CModule::getActive("appFine")) {
                    if ($sibling->countConnections() == 0) {
                        return static::authFailed(
                            "AppFine-msg-Failed first auth",
                            $sibling->user_username,
                            $sibling->_id,
                            static::$instance->auth_method
                        );
                    }
                }

                $sibling->user_login_errors++;
                $sibling->store();

                return static::authFailed(
                    "Auth-failed-combination",
                    $sibling->user_username,
                    $sibling->_id,
                    static::$instance->auth_method,
                );
            }

            return static::authFailed(
                "Auth-failed-combination",
                $sibling->user_username,
                $sibling->_id,
                static::$instance->auth_method
            );
        }

        return true;
    }

    /**
     * Load the stored user preference from the database
     *
     * @param string  $key     Preference's key
     * @param integer $user_id User ID to get the preference of
     *
     * @return array|false
     */
    static function loadPref($key, $user_id)
    {
        $ds = CSQLDataSource::get("std");

        $query = "SELECT `user_id`, `value`
              FROM user_preferences
              WHERE `key` = '$key'";
        $pref  = $ds->loadHashList($query);

        // User preference
        if (isset($pref[$user_id])) {
            return $pref[$user_id];
        }

        // Profile preference
        $query      = "SELECT `profile_id`
              FROM users
              WHERE `user_id` = '$user_id'";
        $profile_id = $ds->loadResult($query);

        if ($profile_id && isset($pref[$profile_id])) {
            return $pref[$profile_id];
        }

        // Default preference
        if (isset($pref[""])) {
            return $pref[""];
        }

        return false;
    }

    /**
     * Load the stored user preferences from the database into cache
     *
     * @param integer $user_id User ID, 0 for default preferences
     *
     * @return void
     */
    static function loadPrefs($user_id = null)
    {
        $ds = CSQLDataSource::get("std");
        if ($ds->loadField("user_preferences", "pref_name")) {
            // Former pure SQL system
            $query      = "SELECT pref_name, pref_value
        FROM user_preferences
        WHERE pref_user = '$user_id'";
            $user_prefs = $ds->loadHashList($query);
        } else {
            // Latter object oriented system
            $user_prefs = CPreferences::get($user_id);
        }

        self::$instance->user_prefs = array_merge(self::$instance->user_prefs, $user_prefs);
    }

    /**
     * Build preferences for connected user, with the default/profile/user strategy
     *
     * @return void
     */
    static function buildPrefs()
    {
        // Default
        self::loadPrefs();

        // Profile
        $user = CUser::get();
        if ($user->profile_id) {
            self::loadPrefs($user->profile_id);
        }

        // User
        self::loadPrefs($user->_id);
    }


    /**
     * Get a named user preference value
     *
     * @param string $name    Name of the user preference
     * @param string $default [optional] A default value when preference is not set
     *
     * @return string The value
     */
    static function pref($name, $default = null)
    {
        $prefs = self::$instance->user_prefs;

        return isset($prefs[$name]) ? $prefs[$name] : $default;
    }

    /**
     * Load locales from cache or build the cache
     *
     * @return void
     */
    public static function loadLocales()
    {
        $lang = CAppUI::pref("LOCALE", "fr");

        self::$lang = $lang;

        $shared_name = "locales-$lang";

        $cache = Cache::getCache(Cache::OUTER);

        $locales_prefixes = $cache->get("$shared_name-" . self::LOCALES_PREFIX);

        // Load from shared memory if possible
        if ($locales_prefixes) {
            return;
        }

        $mutex = new CMbFileMutex("locales-build");
        $mutex->acquire(5);

        $locales_prefixes = $cache->get("$shared_name-" . self::LOCALES_PREFIX);

        // Load from shared memory if possible
        if ($locales_prefixes) {
            $mutex->release();

            return;
        }

        $start_build = microtime(true);
        $locales = [];

        // fix opcache bug when include locales files (winnt & php7.1)
        if (PHP_OS == 'WINNT' && function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach (self::getLocaleFilesPaths($lang) as $_path) {
            include_once $_path;
        }


        $locales = CMbString::filterEmpty($locales);
        foreach ($locales as &$_locale) {
            $_locale = CMbString::unslash($_locale);
        }
        unset($_locale);

        // Load overwritten locales if the table exists
        $overwrite = new CTranslationOverwrite();
        if ($overwrite->isInstalled()) {
            $locales = $overwrite->transformLocales($locales, $lang);
        }

        // Prefix = everything before "." and "-"
        $by_prefix = [];
        $hashes    = [];

        foreach ($locales as $_key => $_value) {
            /** @var string $_prefix */
            /** @var string $_rest */
            /** @var string $_prefix_raw */

            [$_prefix, $_rest, $_prefix_raw] = self::splitLocale($_key);

            if (!isset($by_prefix[$_prefix])) {
                $hashes[$_prefix]    = $_prefix_raw;
                $by_prefix[$_prefix] = [];
            }

            $by_prefix[$_prefix][$_rest] = $_value;
        }

        foreach ($by_prefix as $_prefix => $_locales) {
            self::$locales[$_prefix]        = $_locales;
            self::$locales_loaded[$_prefix] = true;

            $cache->set("$shared_name-$_prefix", $_locales);
        }

        $cache->set("$shared_name-" . self::LOCALES_PREFIX, $hashes);

        CApp::log(
            sprintf(
                'CACHE : Took %4.3f ms to build locales %s cache',
                (microtime(true) - $start_build) * 1000,
                $lang
            )
        );

        $mutex->release();
    }

    /**
     * Returns the list of $locale files
     *
     * @param string $locale The locale name of the paths
     *
     * @return array The paths
     */
    static function getLocaleFilesPaths($locale)
    {
        $root_dir = CAppUI::conf("root_dir");

        $overload = glob("$root_dir/modules/*/locales/$locale.overload.php");
        sort($overload);

        $paths = array_merge(
            glob("$root_dir/locales/$locale/*.php"),
            glob("$root_dir/modules/*/locales/$locale.php"),
            glob("$root_dir/style/*/locales/$locale.php"),
            glob("$root_dir/mobile/modules/*/locales/$locale.php"),
            $overload
        );

        return $paths;
    }

    /**
     * Check translated statement exists
     *
     * @param string $str statement to translate
     *
     * @return boolean if translated statement exists
     */
    static function isTranslated($str)
    {
        if (CAppUI::conf('locale_warn')) {
            $char = CAppUI::conf('locale_alert');

            return CAppUI::tr($str) !== $char . $str . $char;
        } else {
            return CAppUI::tr($str) !== $str;
        }
    }

    /**
     * Check configuration to enable Pro Sante Connect login button
     * @return bool
     * @throws Exception
     */
    public static function isLoginPSCEnabled(): bool
    {
        return static::conf('admin ProSanteConnect enable_psc_authentication')
            && static::conf('admin ProSanteConnect enable_login_button');
    }

    /**
     * Return Pro Sante Connect options
     * @return array
     * @throws Exception
     */
    public static function getPSCOptions(): array
    {
        return [
            'clientId'     => static::conf('admin ProSanteConnect client_id'),
            'clientSecret' => static::conf('admin ProSanteConnect client_secret'),
            'redirectUri'  => static::conf('admin ProSanteConnect redirect_uri'),
        ];
    }

    /**
     * Check configuration to enable Pro Sante Connect login button
     * @return bool
     * @throws Exception
     */
    public static function isLoginFCEnabled(): bool
    {
        return static::conf('admin FranceConnect enable_fc_authentication')
            && static::conf('admin FranceConnect enable_login_button');
    }

    /**
     * Return Pro Sante Connect options
     * @return array
     * @throws Exception
     */
    public static function getFCOptions(): array
    {
        return [
            'clientId'          => static::conf('admin FranceConnect client_id'),
            'clientSecret'      => static::conf('admin FranceConnect client_secret'),
            'redirectUri'       => static::conf('admin FranceConnect redirect_uri'),
            'logoutRedirectUri' => static::conf('admin FranceConnect logout_redirect_uri'),
        ];
    }

    /**
     * @param CUser $user
     */
    static function setRedirectFromUserPref(CUser $user)
    {
        $redirect = CValue::request("redirect");
        if (!$redirect || (str_starts_with($redirect, 'psc') || str_starts_with($redirect, 'fc'))) {
            $pref = CPreferences::getAllPrefsForList($user, ['DEFMODULE']);
            [$module, $action] = explode('-', $pref['DEFMODULE']);
            $_REQUEST['redirect'] = "m={$module}&a={$action}";
        }
    }


    /**
     * Get locale prefix CRC32 hash
     *
     * @param string $prefix_raw Prefix, raw value
     *
     * @return string
     */
    static function getLocalePrefixHash($prefix_raw)
    {
        // Cache of [prefix => crc32(prefix)]
        static $prefixes = [];

        if ($prefix_raw === "__common__") {
            return $prefix_raw;
        }

        if (isset($prefixes[$prefix_raw])) {
            return $prefixes[$prefix_raw];
        }

        $prefix                = hash("crc32", $prefix_raw); // Faster than crc32() and md5()
        $prefixes[$prefix_raw] = $prefix;

        return $prefix;
    }

    /**
     * Split a locale key into prefix + rest
     *
     * @param string $str The locale key to split
     *
     * @return array
     */
    static function splitLocale($str)
    {
        $positions = [
            strpos($str, "-"),
            strpos($str, "."),
        ];
        $positions = array_filter($positions);

        // If a separator is present, it's a prefixed locale
        if (count($positions) && ($min_pos = min($positions))) {
            $_prefix_raw = substr($str, 0, $min_pos);
            $_rest       = substr($str, $min_pos);
        } // else a common locales
        else {
            $_prefix_raw = "__common__";
            $_rest       = $str;
        }

        $_prefix = self::getLocalePrefixHash($_prefix_raw);

        return [$_prefix, $_rest, $_prefix_raw];
    }

    /**
     * Localize given statement
     *
     * @param string      $str  Statement to translate
     * @param array|mixed $args Array or any number of sprintf-like arguments
     *
     * @return string translated statement
     */
    static function tr($str, $args = null)
    {
        $initial_args = func_get_args();

        if (empty($str)) {
            return "";
        }

        $str = trim($str);

        // Defined and not empty
        if (self::$localize) {
            [$_prefix, $_rest] = CAppUI::splitLocale($str);

            $_lang_prefix = self::$lang . $_prefix;

            // Not in self::$locales cache
            if (empty(self::$locales_loaded[$_lang_prefix])) {
                $shared_name = "locales-" . self::$lang . "-" . $_prefix;

                $cache = Cache::getCache(Cache::OUTER);

                if ($cache->has($shared_name)) {
                    $_loc = $cache->get($shared_name);

                    if (empty(self::$locales[$_prefix])) {
                        self::$locales[$_prefix] = $_loc;
                    } else {
                        self::$locales[$_prefix] = array_merge(self::$locales[$_prefix], $_loc);
                    }
                }

                self::$locales_loaded[$_lang_prefix] = true;
            }

            // dereferecing makes the systme a lots slower ! :(
            //$by_prefix = array_key_exists($_prefix, self::$locales) ? self::$locales[$_prefix] : null;
            //$by_prefix = self::$locales[$_prefix];

            if (isset(self::$locales[$_prefix][$_rest]) && self::$locales[$_prefix][$_rest] !== "") {
                $str = self::$locales[$_prefix][$_rest];
            } // Other wise keep it in a stack...
            elseif (self::$lang) {
                if (!in_array($str, self::$unlocalized) && !preg_match(self::$localize_ignore, $str)) {
                    self::$unlocalized[] = $str;
                }
                // ... and decorate
                if (self::$locale_mask) {
                    $str = sprintf(self::$locale_mask, $str);
                }
            }
        }

        if ($args !== null) {
            if (!is_array($args)) {
                $args = $initial_args;
                unset($args[0]);
            }
            if ($args) {
                $str = vsprintf($str, $args);
            }
        }

        return nl2br($str);
    }

    /**
     * Get a flattened version of all cached locales
     *
     * @param string $lang Language: fr, en
     *
     * @return array
     */
    static function flattenCachedLocales($lang)
    {
        $shared_name = "locales-$lang";

        $cache = Cache::getCache(Cache::OUTER);

        $prefixes = $cache->get("$shared_name-" . self::LOCALES_PREFIX);

        if (empty($prefixes)) {
            return [];
        }

        $locales = [];
        foreach ($prefixes as $_hash => $_prefix) {
            $prefixed = $cache->get("$shared_name-$_hash");

            if (!$prefixed) {
                continue;
            }

            if ($_prefix === "__common__") {
                $_prefix = "";
            }

            foreach ($prefixed as $_key => $_value) {
                $locales["$_prefix$_key"] = $_value;
            }
        }

        return $locales;
    }

    /**
     * Get the locales from the translations files (do not search for translation overwrite)
     *
     * @return array
     */
    static function getLocalesFromFiles()
    {
        $locales = CAppUI::flattenCachedLocales(CAppUI::$lang);

        //load old locales
        $locale = CAppUI::pref("LOCALE", "fr");
        foreach (CAppUI::getLocaleFilesPaths($locale) as $_path) {
            include $_path; // Do not include once or you can't get the locales from files multiple times in a single query
        }
        $locales = CMbString::filterEmpty($locales);
        foreach ($locales as &$_locale) {
            $_locale = CMbString::unslash($_locale);
        }

        return $locales;
    }

    /**
     * Add a locale at run-time
     *
     * @param string $prefix Prefix
     * @param string $rest   All after the prefix
     * @param string $value  Locale value
     *
     * @return void
     */
    static function addLocale($prefix, $rest, $value)
    {
        $prefix = self::getLocalePrefixHash($prefix);

        if (empty(self::$locales[$prefix])) {
            self::$locales[$prefix] = [
                $rest => $value,
            ];
        } else {
            self::$locales[$prefix][$rest] = $value;
        }
    }

    /**
     * Add a list of locales at run-time
     *
     * @param string $prefix_raw Prefix
     * @param array  $locales    Locales
     *
     * @return void
     */
    static function addLocales($prefix_raw, array $locales)
    {
        $prefix = self::getLocalePrefixHash($prefix_raw);

        if (empty(self::$locales[$prefix])) {
            self::$locales[$prefix] = $locales;
        } else {
            self::$locales[$prefix] = array_merge(self::$locales[$prefix], $locales);
        }
    }

    /**
     * Get all the available languages
     *
     * @return array All the languages (2 letters)
     */
    static function getAvailableLanguages()
    {
        $languages = [];
        $root_dir  = dirname(__DIR__, 2);
        foreach (glob("{$root_dir}/locales/*", GLOB_ONLYDIR) as $lng) {
            $languages[] = basename($lng);
        }

        return $languages;
    }

    /**
     * Return the configuration setting for a given path
     *
     * @param string                $path    Tokenized path, eg "module class var", dP proof
     * @param CMbObject|string|null $context The context
     *
     * @return mixed
     * @throws Exception
     */
    static function conf($path = "", $context = null)
    {
        if ($context) {
            if ($context === 'static') {
                try {
                    return (ConfigurationManager::get())->getValue($path);
                } catch (Exception $e) {
                    if (PHP_SAPI === 'cli') {
                        return null;
                    } else {
                        throw $e;
                    }
                }
            }

            if ($context instanceof CMbObject) {
                $context = $context->_guid;
            }

            return CConfiguration::getValue($context, $path);
        }

        global $dPconfig;
        $conf = $dPconfig;
        if (!$path) {
            return $conf;
        }

        if (!$conf) {
            return null;
        }

        $items = explode(' ', $path);

        try {
            foreach ($items as $part) {
                // dP ugly hack
                if (!array_key_exists($part, $conf) && array_key_exists("dP$part", $conf)) {
                    $part = "dP$part";
                }

                if (!array_key_exists($part, $conf)) {
                    throw new TypeError('Undefined array key "' . $part . '"');
                }

                $conf = $conf[$part];
            }
        } catch (TypeError $exception) {
            CApp::log($exception->getMessage(), null, LoggerLevels::LEVEL_WARNING);
            return null;
        }


        return $conf;
    }

    /**
     * Get the CGroups configuration settings for a given path
     *
     * @param string       $path     Configuration path
     * @param integer|null $group_id CGroups IND
     *
     * @return mixed
     */
    static function gconf($path = '', $group_id = null)
    {
        $group_id = ($group_id) ?: CGroups::get()->_id;

        return static::conf($path, "CGroups-{$group_id}");
    }

    /**
     * Change a configuration value
     *
     * @param string $path           Configuration path
     * @param null   $value          New value
     * @param bool   $allow_all_user Allow all users to store the conf. Be carefull with this argument.
     *
     * @return mixed|null Old value
     * @throws Exception
     */
    static function setConf($path = "", $value = null, bool $allow_all_user = false)
    {
        if (!$allow_all_user && !CAppUI::$user->isAdmin()) {
            return null;
        }

        $config    = new CMbConfig();
        $old_value = $config->get($path);

        $config->set($path, $value);
        $config->update($config->values, true);

        if (CAppUI::conf("config_db")) {
            $forbidden = false;
            foreach (CMbConfig::$forbidden_values as $_value) {
                if (strpos($path, $_value) !== false) {
                    $forbidden = true;
                    break;
                }
            }

            if (!$forbidden) {
                CMbConfig::setConfInDB($path, $value);
            }
        }

        return $old_value;
    }

    /**
     * Produce a unique ID in the HTTP request scope
     *
     * @return integer The ID
     */
    static function uniqueId()
    {
        static $unique_id = 0;

        return $unique_id++;
    }

    /**
     * Go to the "offline" page, specifying a a reason
     *
     * @param string $context The context of the denial
     *
     * @return void
     */
    static function accessDenied($context = null)
    {
        // FIXME use locales
        self::stepMessage(
            UI_MSG_ALERT,
            "common-msg-You are not allowed to access this information (%s)",
            $context
        );

        header("HTTP/1.0 403 Forbidden", true, 403);

        CApp::rip();
    }

    /**
     * Go to the "offline" page, specifying a a reason
     *
     * @param string $object_guid The context of the denial
     *
     * @return void
     */
    static function notFound($object_guid)
    {
        [$class, $id] = explode('-', $object_guid);

        // FIXME use locales
        self::stepMessage(
            UI_MSG_WARNING,
            "common-msg-Object of class %s and ID %d was not found",
            self::tr($class),
            $id
        );

        header("HTTP/1.0 404 Object not found", true, 404);

        CApp::rip();
    }

    /**
     * Check if session is up to date by comparing with module versions
     *
     * @return void
     * @throws Exception
     */
    static function checkSessionUpdate()
    {
        $instance = CAppUI::$instance;

        if (!$instance->user_id) {
            return;
        }

        $query = "SELECT GROUP_CONCAT(`mod_name`, `mod_version`) FROM `modules`";
        $hash  = CSQLDataSource::get("std")->loadResult($query);
        $hash  .= CApp::getVersion()->getBuild();

        $hash = md5($hash);

        if (!isset($instance->update_hash) || $instance->update_hash != $hash) {
            self::buildPrefs();
            $instance->update_hash = $hash;
        }
    }

    /**
     * Tell if session is expired
     *
     * @return bool
     */
    static function isTokenSessionExpired()
    {
        if (!CAppUI::$token_session || !CAppUI::$token_expiration) {
            return false;
        }

        return CMbDT::dateTime() >= CAppUI::$token_expiration;
    }

    /**
     * Get authentification method
     *
     * @return string
     */
    static function getAuthMethod()
    {
        return self::$instance->auth_method;
    }

    /**
     * @return bool
     */
    public static function isAuthBasic(): bool
    {
        return self::$instance->auth_method === CUserAuthentication::AUTH_METHOD_BASIC;
    }

    /**
     * Checks if connected user is the SUPER admin *o*
     *
     * @return bool
     */
    static function isSuperAdmin()
    {
        return (self::$instance->user_id === '1');
    }

    /**
     * Checks if connected user is a CPatient
     *
     * @return bool
     */
    static function isPatient()
    {
        return CUser::isPatientUser(self::$instance->user_type);
    }

    /**
     * Check if the connection is remote or local
     *
     * @return bool
     * @throws Exception
     */
    static function isIntranet()
    {
        $adress                       = CMbServer::getRemoteAddress();
        self::$instance->ip           = $adress["client"];
        self::$instance->proxy        = $adress["proxy"];
        self::$instance->_is_intranet =
            CMbString::isIntranetIP(self::$instance->ip) &&
            (self::$instance->ip != self::conf("system reverse_proxy"));

        return self::$instance->_is_intranet;
    }


    /**
     * Tells if we have to restrict user creation (for LDAP-only)
     *
     * @return bool
     */
    static function restrictUserCreation()
    {
        $group = CGroups::get();
        $user  = CMediusers::get();

        return (
            static::conf('admin LDAP ldap_connection')
            && static::conf('admin CLDAP restrict_new_users_to_LDAP', $group->_guid)
            && !$user->isAdmin()
        );
    }

    /**
     * Gets the last user authentication object
     *
     * @param string $session_id Session id
     *
     * @return CUserAuthentication|null
     */
    static function getLastUserAuthentication($session_id = null)
    {
        $current_session_id = session_id();

        // Check if user is logged in
        if (!self::$instance || !self::$instance->user_id) {
            return null;
        }

        if (($current_session_id === $session_id || !$session_id)
            && self::$instance->_ref_last_auth
            && self::$instance->_ref_last_auth->_id
        ) {
            return self::$instance->_ref_last_auth;
        }

        return CUserAuthentication::getLast($session_id);
    }

    /**
     * Sets user authentication expiration datetime for effective session closing
     *
     * @param string      $datetime   Datetime
     * @param null|string $session_id Session id
     *
     * @return bool
     */
    static function setUserAuthExpirationDatetime($datetime, $session_id = null)
    {
        $last_auth = self::getLastUserAuthentication($session_id);
        if ($last_auth && $last_auth->_id) {
            $last_auth->expiration_datetime = $datetime;
            $last_auth->last_session_update = CMbDT::dateTime();
            $last_auth->nb_update++;

            return !$last_auth->store();
        }

        return false;
    }

    /**
     * Updates user authentication expiration datetime with the user session max lifetime for theorical session closing
     *
     * @param null $session_id
     *
     * @return void
     */
    static function updateUserAuthExpirationDatetime($session_id = null)
    {
        if (!static::reviveSession()) {
            return;
        }
        $user_auth = self::getLastUserAuthentication($session_id);
        $dtnow     = CMbDT::dateTime();

        if ($user_auth
            && ($dtnow > CMbDT::dateTime("+ 60 second", $user_auth->last_session_update))
            && ($dtnow < $user_auth->expiration_datetime)
        ) {
            self::setUserAuthExpirationDatetime(
                CMbDT::dateTime("+ $user_auth->session_lifetime second", $dtnow),
                $session_id
            );
        }
    }

    /**
     * Check whether or not patient are partitioned by function
     *
     * @return bool
     */
    static function isCabinet()
    {
        return CAppUI::conf('dPpatients CPatient function_distinct') == 1;
    }

    /**
     * Check whether or not patient are partitioned by group
     *
     * @return bool
     */
    static function isGroup()
    {
        return CAppUI::conf('dPpatients CPatient function_distinct') == 2;
    }

    /**
     * Get the remaining days of a password from session
     *
     * @return mixed
     */
    static function checkPasswordRemainingDays()
    {
        return CValue::sessionAbs(static::PASSWORD_REMAINING_DAYS);
    }

    /**
     * Empty the variable telling that the password has to be renewed within N days
     *
     * @return void
     */
    static function resetPasswordRemainingDays()
    {
        if (static::checkPasswordRemainingDays()) {
            CValue::setSessionAbs(self::PASSWORD_REMAINING_DAYS, null);
        }
    }

    /**
     * Return the accounts and number of unread messages for the connected user
     *
     * @return array
     */
    public static function getMessagerieInfo()
    {
        if (!CModule::getActive('messagerie') || !CModule::getCanDo('messagerie')->read) {
            return null;
        }

        return ['accounts' => self::getMessagerieAccounts(), 'counters' => self::getMessagerieCounters()];
    }

    /**
     * Return the number of unread messages for each messagerie account of the connected user
     *
     * @return array|null
     */
    static function getMessagerieCounters()
    {
        if (!CModule::getActive('messagerie') || !CModule::getCanDo('messagerie')->read) {
            return null;
        }

        $data = [];

        $categories = self::getMessagerieAccounts();
        foreach ($categories as $category => $accounts) {
            if ($category == 'internal') {
                $count           = CUserMessageDest::getUnreadMessages();
                $data[$category] = ['total' => $count, $count];
            } elseif (is_array($accounts)) {
                $data[$category] = ['total' => 0];
                foreach ($accounts as $guid => $object) {
                    if (is_object($object)) {
                        $data[$category][$guid]   = $object->getUnreadMessages();
                        $data[$category]['total'] += $data[$category][$guid];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Return the messagerie accounts (internal, external, apicrypt, mssante, medimail, mondialSante) of the connected
     * users
     *
     * @return array
     */
    public static function getMessagerieAccounts()
    {
        if (!CModule::getActive('messagerie') || !CModule::getCanDo('messagerie')->read) {
            return [];
        }
        $user = CMediusers::get();

        $cache = new Cache(
            self::MESSAGERIE_ACCOUNTS_CACHE,
            "accounts-{$user->_guid}",
            Cache::OUTER,
            CUserMessageDest::getCacheLifetime()
        );

        $accounts = ['internal' => [], 'external' => [], 'laboratories' => []];

        if ($cache->exists()) {
            $accounts = $cache->get();
        } else {
            if (CAppUI::gconf('messagerie access allow_internal_mail')) {
                $accounts['internal'][] = true;
            } else {
                unset($accounts['internal']);
            }

            if (CAppUI::gconf('messagerie access allow_external_mail')) {
                $pop_accounts = CSourcePOP::getAccountsFor($user);
                foreach ($pop_accounts as $account) {
                    $accounts['external'][$account->_guid] = $account;
                }
            }

            if (CModule::getActive('apicrypt') && CModule::getCanDo('apicrypt')->read) {
                $apicrypt_account = CSourcePOP::getApicryptAccountFor($user);

                if ($apicrypt_account->_id) {
                    $accounts['external'][$apicrypt_account->_guid] = $apicrypt_account;
                }
            }

            if (CModule::getActive('mssante') && CModule::getCanDo('mssante')->read) {
                $mssante_account = CMSSanteUserAccount::getAccountFor($user);
                if ($mssante_account->_id) {
                    $accounts['external'][$mssante_account->_guid] = $mssante_account;
                }
            }

            if (CModule::getActive('medimail') && CModule::getCanDo('medimail')->read) {
                $medimail_account = CMedimailAccount::getAccountFor($user);
                if ($medimail_account->_id) {
                    $accounts['external'][$medimail_account->_guid] = $medimail_account;
                }

                if (self::pref("allowed_access_application_mailbox") === "1") {
                    $medimail_account_application = CGroups::get()->loadRefMedimailAccount();
                    if ($medimail_account_application->_id) {
                        $accounts['external'][$medimail_account_application->_guid] = $medimail_account_application;
                    }
                }

                if (self::pref("allowed_access_organisational_mailbox") === "1") {
                    $medimail_account_organisational = CFunctions::getCurrent()->loadRefMedimailAccount();
                    if ($medimail_account_organisational->_id) {
                        $accounts['external'][$medimail_account_organisational->_guid]
                            = $medimail_account_organisational;
                    }
                }
            }

            if (CModule::getActive('mondialSante') && CModule::getCanDo('mondialSante')->read) {
                $mondial_sante_account = CMondialSanteAccount::getAccountFor($user);
                if ($mondial_sante_account->_id) {
                    $accounts['laboratories'][$mondial_sante_account->_guid] = $mondial_sante_account;
                }
            }

            if (empty($accounts['laboratories'])) {
                unset($accounts['laboratories']);
            }

            $cache->put($accounts);
        }

        return $accounts;
    }

    /**
     * Reset the messagerie accounts cache
     *
     * @return void
     */
    public static function resetMessagerieAccountsCache()
    {
        $user  = CMediusers::get();
        $cache = new Cache(
            self::MESSAGERIE_ACCOUNTS_CACHE,
            "accounts-{$user->_guid}",
            Cache::OUTER,
            CUserMessageDest::getCacheLifetime()
        );
        $cache->rem();
    }

    /**
     * Tells if we must update session lifetime
     *
     * @return bool
     */
    static function reviveSession()
    {
        return !CAppUI::$session_no_revive;
    }

    /**
     * Set static::instance after Auth
     * Used by Authentication::doAuth
     *
     * @param CAuthentication $authentication
     *
     * @throws Exception
     */
    static function setInstance(CAuthentication $authentication)
    {
        // CUser from authentication
        $_user = $authentication->user;

        // Convert CUser to CMediuser for mb compat
        static::$user = new CMediusers();
        if (static::$user->isInstalled()) {
            static::$user->load($_user->_id);
            static::$instance->_ref_user = static::$user;
        }

        static::$instance->auth_method = $authentication->method;
        static::$instance->user_remote = $authentication->user_remote;
        static::$instance->user_group  = $authentication->user_group;
        static::$instance->user_id     = $_user->_id;

        static::$instance->ua             = $authentication->auth ? $authentication->auth->_ref_user_agent : new CUserAgent(
        );
        static::$instance->_ref_last_auth = $authentication->auth;

        static::$instance->user_first_name = $_user->user_first_name;
        static::$instance->user_last_name  = $_user->user_last_name;
        static::$instance->user_email      = $_user->user_email;
        static::$instance->user_type       = $_user->user_type;
        static::$instance->user_last_login = $_user->getLastLogin();
    }

    public static function create(
        CUser                $user,
        string               $method,
        ?WeakPasswordBadge   $weak_password_badge,
        ?CUserAuthentication $auth
    ): void {
        $user_group  = null;
        $user_remote = false;

        // Convert CUser to CMediusers for mb compat
        static::$user = new CMediusers();
        if (static::$user->isInstalled()) {
            static::$user->load($user->_id);
            static::$instance->_ref_user = static::$user;

            if (static::$user->_id) {
                $user_group  = static::$user->loadRefFunction()->group_id;
                $user_remote = (bool)static::$user->remote;
            }
        }

        static::$instance->auth_method = $method;
        static::$instance->user_remote = $user_remote;
        static::$instance->user_group  = $user_group;
        static::$instance->user_id     = $user->_id;

        static::$instance->weak_password = ($weak_password_badge && $weak_password_badge->isEnabled());

        static::$instance->ua             = new CUserAgent();
        static::$instance->_ref_last_auth = $auth;

        static::$instance->user_first_name = $user->user_first_name;
        static::$instance->user_last_name  = $user->user_last_name;
        static::$instance->user_email      = $user->user_email;
        static::$instance->user_type       = $user->user_type;
        static::$instance->user_last_login = $user->getLastLogin();
    }

    public static function initUser(): void
    {
        if (CModule::getInstalled('mediusers')) {
            self::$user = new CMediusers();
            if (self::$user->isInstalled()) {
                self::$user->load(self::$instance->user_id);
                self::$user->getBasicInfo();
                self::$instance->_ref_user =& self::$user;

                CApp::$is_robot = self::$user->isRobot();
            }
        }
    }

    /**
     * Retourne l'état d'activation du mode dark et du theme Mediboard Etendu
     *
     * @return bool
     */
    static function isMediboardExtDark()
    {
        return self::pref("mediboard_ext_dark") === '1';
    }

    /**
     * @param string $locale
     * @param string $value
     * @param bool   $add_slashes
     *
     * @return bool
     */
    public static function localExists(string $locale, string $value = null, bool $add_slashes = false): bool
    {
        $char = CAppUI::conf('locale_warn') ? CAppUI::conf('locale_alert') : '';

        $trad = CAppUI::tr($locale);

        $local_exists = (bool)($locale !== $char . $trad . $char);

        if (!$value) {
            return $local_exists;
        }

        $trad = $add_slashes ? addslashes($trad) : $trad;

        return (bool)($trad == $value);
    }

    public static function loadCoreLocales(): array
    {
        // set locales depend if user is connected
        require CAppUI::conf('root_dir') . "/locales/core.php";

        if (empty($locale_info["names"])) {
            $locale_info["names"] = [];
        }
        setlocale(LC_TIME, $locale_info["names"]);

        if (empty($locale_info["charset"])) {
            $locale_info["charset"] = "UTF-8";
        }

        // We don't use mb_internal_encoding as it may be redefined by libs
        CApp::$encoding = $locale_info["charset"];

        ini_set('default_charset', CApp::$encoding);

        return CAppUI::$locale_info = $locale_info;
    }

    public static function isMultiTabMessageRead(): int
    {
        // For JS usage
        return CValue::sessionAbs(self::MULTI_TAB_MSG_READ, 0);
    }

    public static function markMultiTabMessageAsRead(): void
    {
        CValue::setSessionAbs(self::MULTI_TAB_MSG_READ, 1);
    }

    public static function getCacheNS(): string
    {
        return preg_replace('/[^\w]+/', '_', CAppUI::conf('root_dir'));
    }
}
