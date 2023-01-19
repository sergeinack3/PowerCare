<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Core\Sessions\CSessionManager;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * User authentication
 */
class CUserAuthentication extends CMbObject
{
    public const AUTH_METHOD_BASIC        = 'basic';
    public const AUTH_METHOD_SUBSTITUTION = 'substitution';
    public const AUTH_METHOD_LDAP         = 'ldap';
    public const AUTH_METHOD_LDAP_GUID    = 'ldap_guid';
    public const AUTH_METHOD_TOKEN        = 'token';
    public const AUTH_METHOD_CARD         = 'card';
    public const AUTH_METHOD_SSO          = 'sso';
    public const AUTH_METHOD_REACTIVE     = 'reactivation';
    public const AUTH_METHOD_STANDARD     = 'standard';
    public const AUTH_METHOD_OAUTH        = 'oauth';

    public const AUTH_METHODS = [
        self::AUTH_METHOD_BASIC,
        self::AUTH_METHOD_SUBSTITUTION,
        self::AUTH_METHOD_LDAP,
        self::AUTH_METHOD_LDAP_GUID,
        self::AUTH_METHOD_TOKEN,
        self::AUTH_METHOD_CARD,
        self::AUTH_METHOD_SSO,
        self::AUTH_METHOD_REACTIVE,
        self::AUTH_METHOD_STANDARD,
        self::AUTH_METHOD_OAUTH,
    ];

    public $user_authentication_id;

    public $user_id;
    public $previous_auth_id;

    // Not used anymore
    public $authentication_factor_id;

    public $auth_method;
    public $datetime_login;
    public $expiration_datetime;
    public $last_session_update;
    public $nb_update;
    public $session_lifetime; // in minutes
    public $ip_address;
    public $session_id;

    // Screen
    public $screen_width;
    public $screen_height;

    // User agent
    public $user_agent_id;

    /** @var CUser */
    public $_ref_user;

    /** @var CUserAuthentication */
    public $_ref_previous_auth;

    /** @var CUserAgent */
    public $_ref_user_agent;

    // Form fields
    /** @var string */
    public $_start_date;

    /** @var string */
    public $_end_date;

    /** @var string */
    public $_expiration_start_date;

    /** @var string */
    public $_expiration_end_date;

    /** @var string */
    public $_auth_method;

    /** @var string */
    public $_user_type;

    /** @var string */
    public $_session_type;

    /** @var string */
    public $_activity_duration;

    /** @var string */
    public $_session_duration;

    /** @var string */
    public $_domain;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = "user_authentication";
        $spec->key      = "user_authentication_id";
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                     = parent::getProps();
        $props["user_id"]          = "ref notNull class|CUser back|authentications";
        $props["previous_auth_id"] = "ref class|CUserAuthentication back|authentications_previous";

        // Not used anymore
        $props["authentication_factor_id"] = "num";

        $props["auth_method"]         = "enum list|" . implode('|', self::AUTH_METHODS);
        $props["datetime_login"]      = "dateTime notNull";
        $props["expiration_datetime"] = "dateTime";
        $props["last_session_update"] = "dateTime";
        $props["nb_update"]           = "num";
        $props["session_lifetime"]    = "num";
        $props["ip_address"]          = "str notNull";
        $props["session_id"]          = "str notNull show|0";

        // Screen
        $props["screen_width"]  = "num";
        $props["screen_height"] = "num";

        // User agent
        $props["user_agent_id"] = "ref class|CUserAgent back|user_authentications";

        $props['_start_date']            = 'dateTime';
        $props['_end_date']              = 'dateTime moreThan|_start_date';
        $props['_expiration_start_date'] = 'dateTime';
        $props['_expiration_end_date']   = 'dateTime moreThan|_expiration_start_date';
        $props['_auth_method']           = 'set list|' . implode('|', self::AUTH_METHODS);
        $props['_user_type']             = 'enum list|all|human|bot';
        $props['_session_type']          = 'enum list|all|active|expired';
        $props['_activity_duration']     = 'str';
        $props['_session_duration']      = 'str';
        $props['_domain']                = 'enum list|all|group|function default|group';

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_activity_duration = CMbDT::minutesRelative($this->datetime_login, $this->last_session_update);

        if ($this->_activity_duration !== null) {
            $this->_activity_duration .= ' min';
        }

        $this->_session_duration = round($this->session_lifetime / 60) . ' min';
    }

    /**
     * @inheritDoc
     */
    public function store(): ?string
    {
        if ($msg = parent::store()) {
            return $msg;
        }

        // Because previous $user->getLastLogin() set the $user->_user_last_login field and that value is obsolete
        $cache = Cache::getCache(Cache::INNER);
        $cache->delete('user-last_login');

        return null;
    }

    /**
     * Tells if the "user_authentication" table exists
     *
     * @return bool
     */
    static function authReady()
    {
        $that = new self();

        return $that->isFieldInstalled("nb_update");
    }

    /**
     * Log user authentication
     *
     * @param CUser   $user             The user logging-in
     * @param integer $previous_user_id Previous User ID (in case of substitution login)
     * @param string  $auth_method      used in context index_api bu CAuthentication
     * @param bool    $is_stateless     used in api context when mode auth are stateless
     *
     * @return self
     */
    static function logAuth(CUser $user, $previous_user_id = null, bool $is_stateless = false)
    {
        if (!self::authReady() || $user->dont_log_connection) {
            return;
        }

        global $rootName;

        if ($is_stateless) {
            // aggregate per day stateless authentications
            $last_auth = $user->loadRefLastAuth();
            if ($last_auth
                && ($last_auth->auth_method === CAppUI::$instance->auth_method)
                && str_starts_with($last_auth->session_id, 'stateless')
                && (CMbDT::date($last_auth->last_session_update) === CMbDT::date())
            ) {
                $last_auth->last_session_update = CMbDT::dateTime();
                $last_auth->nb_update++;
                $last_auth->expiration_datetime = CMbDT::dateTime();
                $last_auth->store();

                return $last_auth;
            }
        }

        $session_name        = CSessionManager::forgeSessionName($rootName);
        $app                 = CAppUI::$instance;
        $session_maxlifetime = CSessionHandler::getSessionMaxLifetime();
        $dtnow               = CMbDT::dateTime();

        $auth                      = new self;
        $auth->user_id             = $user->_id;
        $auth->previous_auth_id    = ($previous_user_id) ? self::getCurrentAuthenticationID() : null;
        $auth->auth_method         = $app->auth_method;
        $auth->datetime_login      = $dtnow;
        $auth->last_session_update = $dtnow;
        $auth->nb_update           = 0;
        $auth->ip_address          = $app->ip;
        if (session_id() === '') {
            // Api mode
            $auth->session_id          = uniqid('stateless', true);
            $auth->session_lifetime    = 0;
            $auth->expiration_datetime = CMbDT::dateTime();
        } else {
            $auth->session_id          = session_id();
            $auth->session_lifetime    = $session_maxlifetime;
            $auth->expiration_datetime = CMbDT::dateTime("+ $session_maxlifetime second");
        }


        // Screen size
        $cookie = CValue::cookie("$session_name-uainfo");
        $uainfo = stripslashes($cookie);

        if ($uainfo) {
            $uainfo = json_decode($uainfo, true);
            if (isset($uainfo["screen"])) {
                $screen              = $uainfo["screen"];
                $auth->screen_width  = (int)$screen[0];
                $auth->screen_height = (int)$screen[1];
            }
        }

        // User agent
        $user_agent            = CUserAgent::create(true);
        $auth->user_agent_id   = $user_agent->_id;
        $auth->_ref_user_agent = $user_agent;

        // In order
        CAppUI::$instance->user_prev_login = $user->getLastLogin();

        $auth->store();

        // Because previous $user->getLastLogin() set the $user->_user_last_login field and that value is obsolete
        $user->_user_last_login = null;

        return $auth;
    }


    /**
     * @inheritdoc
     */
    function loadView()
    {
        parent::loadView();

        $this->loadRefUser();
        $this->_view = "{$this->_ref_user} (" . CAppUI::tr("{$this->_class}.auth_method.{$this->auth_method}") . ')';
    }

    /**
     * Get user
     *
     * @return CUser
     */
    function loadRefUser()
    {
        return $this->_ref_user = $this->loadFwdRef("user_id", true);
    }

    /**
     * Get user agent
     *
     * @return CUserAgent
     */
    function loadRefUserAgent()
    {
        return $this->_ref_user_agent = $this->loadFwdRef("user_agent_id", false);
    }

    /**
     * Get previous user authentication
     *
     * @return CUserAuthentication
     */
    function loadRefPreviousUserAuthentication()
    {
        return $this->_ref_previous_auth = $this->loadFwdRef('previous_auth_id', true);
    }

    /**
     * Get user's current authentication ID
     *
     * @return int
     */
    static function getCurrentAuthenticationID()
    {
        $auth             = new self();
        $auth->session_id = session_id();

        if ($auth->loadMatchingObject('datetime_login DESC')) {
            return $auth->_id;
        }

        return null;
    }

    /**
     * Gets last user authentication object
     *
     * @param string $session_id Session identifier
     *
     * @return CUserAuthentication|null
     */
    static function getLast($session_id)
    {
        if (!$session_id) {
            return null;
        }

        $auth             = new self();
        $auth->session_id = $session_id;
        $auths            = $auth->loadMatchingList();

        // Sort by datetime_login
        usort(
            $auths,
            function ($first, $second) {
                return strtotime($first->datetime_login) < strtotime($second->datetime_login);
            }
        );

        $auth = reset($auths);

        if ($auth) {
            return $auth;
        }

        return null;
    }

    static function getAuthenticatedUserIDS()
    {
        $ds = CSQLDataSource::get('std');

        $request = new CRequest();
        $request->addSelect('DISTINCT user_authentication.user_id');
        $request->addTable('user_authentication');

        $where = [
            'user_authentication.expiration_datetime' => $ds->prepare(
                'IS NOT NULL AND user_authentication.expiration_datetime > ?',
                CMbDT::dateTime()
            ),
        ];

        $request->addWhere($where);

        return $ds->loadColumn($request->makeSelect());
    }

    /**
     * Get the connected users for a given group
     *
     * @param integer|null $group_id CGroups ID
     *
     * @return null|array
     */
    static function getConnectedUsersForGroup($group_id = null)
    {
        $user_ids = static::getAuthenticatedUserIDs();

        if (!$user_ids) {
            return [];
        }

        $group_id = ($group_id) ?: CGroups::get()->_id;

        $user = new CMediusers();
        $ds   = $user->getDS();

        $where = [
            'functions_mediboard.group_id' => $ds->prepare('= ?', $group_id),
            'users_mediboard.user_id'      => $ds->prepareIn($user_ids),
        ];

        $ljoin = [
            'functions_mediboard' => 'functions_mediboard.function_id = users_mediboard.function_id',
        ];

        return $user->loadColumn($user->_spec->key, $where, $ljoin);
    }

    /**
     * Get the connected users for a given function
     *
     * @param integer|null $function_id CFunctions ID
     *
     * @return null|array
     */
    static function getConnectedUsersForFunction($function_id = null)
    {
        $user_ids = static::getAuthenticatedUserIDs();

        if (!$user_ids) {
            return [];
        }

        if (!$function_id) {
            $user = CMediusers::get();

            if (!$user || !$user->_id) {
                return null;
            }

            $function_id = $user->function_id;
        }

        $user = new CMediusers();
        $ds   = $user->getDS();

        $where = [
            'users_mediboard.function_id' => $ds->prepare('= ?', $function_id),
            'users_mediboard.user_id'     => $ds->prepareIn($user_ids),
        ];

        return $user->loadColumn($user->_spec->key, $where);
    }

    /**
     * Set fin_activite for old checkin
     *
     * @return void
     */
    static function inactiverExpiredUsers()
    {
        return;
        CView::enforceSlave();

        $nb_jours = CAppUI::conf('admin CUser force_inactive_old_authentification');
        $date_max = CMBDT::date("-{$nb_jours} days");

        $ds = CSQLDataSource::get('std');

        $request = new CRequest();
        $request->addSelect('u.user_id');
        $request->addTable(['users AS u', 'users_mediboard AS um']);
        $request->addWhere(
            [
                'u.user_id'             => '= um.user_id',
                'u.dont_log_connection' => "= '0'",
                'u.template'            => "= '0'",
                'um.main_user_id'       => 'IS NULL',
                'um.actif'              => "= '1'",
                'um.fin_activite'       => 'IS NULL',
            ]
        );

        // Eligible users
        $user_ids = $ds->loadColumn($request->makeSelect());

        if (!$user_ids) {
            CView::disableSlave();

            return;
        }

        // Filtering SOFTWARE users
        foreach (CGroups::loadGroups() as $_group) {
            if ($tag = CMediusers::getTagSoftware($_group->_id)) {
                if ($tags = CIdSante400::getMatches('CMediusers', $tag, null)) {
                    if ($software_users = CIdSante400::massLoadFwdRef($tags, 'object_id')) {
                        $user_ids = array_diff($user_ids, CMbArray::pluck($software_users, '_id'));
                    }
                }
            }
        }

        if (!$user_ids) {
            CView::disableSlave();

            return;
        }

        $request = new CRequest();
        $request->addSelect('object_id');
        $request->addTable('user_log');
        $request->addWhere(
            [
                'object_id'    => $ds::prepareIn($user_ids),
                'object_class' => "= 'CMediusers'",
                'type'         => "= 'create'",
                'date'         => $ds->prepare('> ?', $date_max),
            ]
        );

        // Filtering fresh users
        if ($fresh_user_ids = $ds->loadColumn($request->makeSelect())) {
            $user_ids = array_diff($user_ids, $fresh_user_ids);
        }

        if (!$user_ids) {
            CView::disableSlave();

            return;
        }

        // Filtering users that logged into the application within n days
        $request = new CRequest();
        $request->addSelect('DISTINCT user_id');
        $request->addTable('user_authentication');
        $request->addWhere(
            [
                'datetime_login' => $ds->prepare('> ?', $date_max),
                'user_id'        => $ds::prepareIn($user_ids),
            ]
        );

        if ($logged_users = $ds->loadColumn($request->makeSelect())) {
            $user_ids = array_diff($user_ids, $logged_users);
        }

        if (!$user_ids) {
            CView::disableSlave();

            return;
        }

        $user  = new CMediusers();
        $users = $user->loadAll($user_ids);

        CView::disableSlave();

        // Change current user to admin (for $log->user_id)
        $current_user              = CAppUI::$instance->user_id;
        CAppUI::$instance->user_id = 1;

        foreach ($users as $_user) {
            $last_login = $_user->getLastLogin();
            $last_login = ($last_login) ? CMbDT::date("+{$nb_jours} days", $last_login) : 'now';

            /**
             * Q&D hack, because of locales/core.php not fully loaded yet, we do need to not alter other fields in order to not break
             * encoding.
             *
             * Loading it would not resolve the problem because of previous user password hashes have been stored with UTF-8 encoding
             * instead of WINDOWS-1252
             */

            $_old_id    = $_user->_id;
            $_old_class = $_user->_class;

            $_user->nullifyProperties();

            $_user->_user_password = null;
            $_user->_id            = $_old_id;
            $_user->_class         = $_old_class;

            $_user->fin_activite = $last_login;
            $_user->store();
        }

        // Undo change
        CAppUI::$instance->user_id = $current_user;

        return;
    }

    /**
     * Tells if User's session is currently active
     *
     * @return bool
     */
    public function isCurrentlyActive()
    {
        if (!$this->expiration_datetime) {
            return true;
        }

        return ($this->expiration_datetime > CMbDT::dateTime());
    }

    /**
     * Get the session activity ratio (number of session update per minute of activity, max one update per minute)
     *
     * @return int
     */
    public function getActivityRatio()
    {
        $reference_date = ($this->isCurrentlyActive()) ? CMbDT::dateTime() : $this->expiration_datetime;

        $since_login = CMbDT::minutesRelative($this->datetime_login, $reference_date);

        if ($since_login === 0) {
            return 100;
        }

        return round(($this->nb_update / $since_login) * 100);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function disconnectSimilarOnes(): void
    {
        if ($this->auth_method === self::AUTH_METHOD_SUBSTITUTION) {
            return;
        }

        $ds                  = $this->getDS();
        $user_id             = $this->user_id;
        $authentication_id   = $this->_id;
        $expiration_datetime = CMbDT::dateTime();
        $session_id          = $this->session_id;

        $where = [
            'user_id'                => $ds->prepare('= ?', $user_id),
            'user_authentication_id' => $ds->prepare('!= ?', $authentication_id),
            'expiration_datetime'    => $ds->prepare('> ?', $expiration_datetime),
            'auth_method'            => $ds->prepare('!= ?', self::AUTH_METHOD_SUBSTITUTION),
            'session_id'             => $ds->prepare('!= ?', $session_id),
        ];

        $auths = $this->loadList($where);

        if ($auths) {
            foreach ($auths as $_auth) {
                CSessionHandler::destroy($_auth->session_id);
            }
        }
    }
}
