<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\FieldSpecs\CBoolSpec;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Mediboard\System\Controllers\Legacy\CMainController;
use stdClass;

/**
 * The VMC view class
 * Responsibilities :
 *  - view reflexion
 *  - view helpers
 */
class CView
{
    /** @var int In order to specify that we do not want to check permission here */
    public const NO_PERM_CHECK = 0;

    /** @var stdClass Parameters values */
    public static $params;

    /** @var string[] Parameters properties */
    public static $props = [];

    /** @var bool View was slaved */
    public static $enslaved = false;

    /** @var bool View is currently slaved */
    public static $slavestate = false;

    /** @var string[] Protected param names */
    public static $protected_names = ["m", "a", "tab", "dialog", "raw", "ajax", "info", "enslave"];

    /** @var bool Whether chekin() done or not */
    public static $checkedin = true;

    /** @var bool Throw warning on session already closed or not */
    public static $session_closed_warning = true;

    /** @var array */
    public static $spec_options = [
        "allow_zero"     => true,
        "allowed_values" => null,
    ];

    /**
     * Get a REQUEST parameter
     *
     * @param string       $name Name of the parameter
     * @param string|array $prop Property specification of the parameter
     * @param int|null     $perm
     *
     * @return mixed
     * @throws Exception
     */
    public static function request(string $name, $prop, ?int $perm = self::NO_PERM_CHECK)
    {
        return self::checkParam($name, $prop, CValue::request($name), $perm);
    }

    /**
     * @param string       $name
     * @param string|array $prop
     *
     * @return mixed
     * @throws Exception
     */
    public static function requestRefCheckRead(string $name, $prop)
    {
        return self::request($name, $prop, PERM_READ);
    }

    /**
     * @param string       $name
     * @param string|array $prop
     *
     * @return mixed
     * @throws Exception
     */
    public static function requestRefCheckEdit(string $name, $prop)
    {
        return self::request($name, $prop, PERM_EDIT);
    }

    /**
     * Get a GET parameter
     *
     * @param string       $name    Name of the parameter
     * @param string|array $prop    Property specification of the parameter
     * @param bool         $session Use session for retrieval if undefined
     * @param int|null     $perm
     *
     * @return mixed
     * @throws Exception
     */
    public static function get(string $name, $prop, ?bool $session = false, int $perm = self::NO_PERM_CHECK)
    {
        if ($session) {
            self::startSession();
            $value = CValue::getOrSession($name);
        } else {
            $value = CValue::get($name);
        }

        return self::checkParam($name, $prop, $value, $perm);
    }

    /**
     * @param string       $name
     * @param string|array $prop
     * @param bool         $session
     *
     * @return mixed
     * @throws Exception
     */
    public static function getRefCheckRead(string $name, $prop, bool $session = false)
    {
        return self::get($name, $prop, $session, PERM_READ);
    }

    /**
     * @param string       $name
     * @param string|array $prop
     * @param bool         $session
     *
     * @return mixed
     * @throws Exception
     */
    public static function getRefCheckEdit(string $name, $prop, bool $session = false)
    {
        return self::get($name, $prop, $session, PERM_EDIT);
    }

    /**
     * Get a POST parameter
     *
     * @param string       $name    Name of the parameter
     * @param string|array $prop    Property specification of the parameter
     * @param bool         $session Use session for retrieval if undefined
     * @param int|null     $perm
     *
     * @return mixed
     * @throws Exception
     */
    public static function post(string $name, $prop, ?bool $session = false, ?int $perm = self::NO_PERM_CHECK)
    {
        if ($session) {
            self::startSession();
            $value = CValue::postOrSession($name);
        } else {
            $value = CValue::post($name);
        }

        return self::checkParam($name, $prop, $value, $perm);
    }

    /**
     * @param string       $name
     * @param string|array $prop
     *
     * @return mixed
     * @throws Exception
     */
    public static function postRefCheckRead(string $name, $prop)
    {
        return self::post($name, $prop, false, PERM_READ);
    }

    /**
     * @param string       $name
     * @param string|array $prop
     *
     * @return mixed
     * @throws Exception
     */
    public static function postRefCheckEdit(string $name, $prop)
    {
        return self::post($name, $prop, false, PERM_EDIT);
    }

    /**
     * Sets a value to the session[$m]. Very useful to nullify object ids after deletion
     *
     * @param string $name  The key to store in the session
     * @param mixed  $value The value to store
     *
     * @return mixed The value
     */
    public static function setSession(string $name, $value = null)
    {
        global $m;
        self::startSession();

        return $_SESSION[$m][$name] = $value;
    }

    /**
     * Check a parameter
     *
     * @param string       $name  Name of the parameter
     * @param string|array $prop  Property specification of the parameter
     * @param mixed        $value Value of the parameter
     * @param int          $perm
     *
     * @return mixed
     * @throws Exception
     */
    private static function checkParam(string $name, $prop, $value, int $perm)
    {
        self::$checkedin     = false;
        if (!self::$params) {
            self::$params = new StdClass();
        }
        self::$params->$name =& $value;

        // Check the name
        if (in_array($name, self::$protected_names)) {
            $error = "View parameter '$name' is a protected name and should NOT be used.";
            trigger_error($error, E_USER_WARNING);
        }

        // Check duplicates
        if (array_key_exists($name, self::$props)) {
            $error = "View parameter '$name' is already in use.";
            trigger_error($error, E_USER_WARNING);
        }

        // Get Specification
        self::$props[$name] = $prop;
        // Avoid setting a default value when a bool spec is used without default
        CBoolSpec::$_default_no = false;
        if (is_array($prop)) {
            $spec = CMbFieldSpecFact::getComplexSpecWithClassName("stdClass", $name, $prop, self::$spec_options);
            $prop = array_shift($prop);
        } else {
            $spec = CMbFieldSpecFact::getSpecWithClassName("stdClass", $name, $prop, self::$spec_options);
        }
        CBoolSpec::$_default_no = true;

        // Defaults the value when available
        if ($value === null && $spec->default !== null) {
            $value = $spec->default;
        }

        // Could be null
        if ($value === "" || $value === null) {
            if (!$spec->notNull) {
                return $value;
            }
        }

        // Check the value
        if ($msg = $spec->checkPropertyValue(self::$params)) {
            $truncated = CMbString::truncate($value);
            $error     = "View parameter '$name' with spec '$prop' has inproper value '$truncated': $msg";
            trigger_error($error, E_USER_WARNING);
        }

        if ($spec instanceof CRefSpec && ($perm !== self::NO_PERM_CHECK)) {
            if (!$spec->checkPermission(self::$params, $perm)) {
                // Todo: Should use Exception, but will be a pain to handle actually
                CAppUI::accessDenied();
            }
        }

        return $value;
    }

    /**
     * Close the parameter list definition and provides inspection information on info mode
     *
     * @return void
     * @throws Exception
     */
    public static function checkin(): void
    {
        self::$checkedin = true;

        if (CValue::request("enslave")) {
            CView::enforceSlave();
        }

        if (CApp::isSessionRestricted()) {
            return;
        }

        if (!CValue::request("info")) {
            CSessionHandler::writeClose();

            return;
        }

        // Dump properties on raw
        if (CValue::request("raw")) {
            CApp::json(self::$props);
        }

        // Finally show properties
        self::info();
        CApp::rip();
    }

    /**
     * Show view properties information
     *
     * @return void
     */
    public static function info(): void
    {
        (new CMainController())->viewInfo(self::$props, self::$params);
    }

    /**
     * Enforce the current view to be rerouted on a slave SQL server if slave datasource is available
     * typical views: stats, reports, any long term data views with  massive SQL queries
     *
     * @param bool $die_error Die if the slave is defined but not reachable
     *
     * @throws Exception
     */
    public static function enforceSlave(bool $die_error = true): void
    {
        if (!CAppUI::conf("enslaving_active")) {
            return;
        }

        if (self::$slavestate) {
            return;
        }

        // Enslaved views are supposably session stallers so close session preventively
        // Si la session est fermée dans un token à accès restreint elle ne pourra plus être détruite dans le CApp:rip
        // car des headers seront envoyés avant qu'on tente de la restart pour la détruire
        if (!CApp::isSessionRestricted()) {
            CSessionHandler::writeClose();
        }

        // URL param enslave prevention
        if (CValue::request("enslave") === "0") {
            return;
        }

        // Test wether a slave datasource has been configured
        if (!CAppUI::conf("db slave dbhost")) {
            return;
        }

        // Check connection to the slave datasource
        // Rip if slave is not reachable
        if (!CSQLDataSource::get("slave", true)) {
            if ($die_error) {
                CAppUI::stepAjax("common-Error-Slave not reachable", UI_MSG_ERROR);
            } else {
                return;
            }
        }

        self::$enslaved   = true;
        self::$slavestate = true;
        self::rerouteStdDS();
    }

    /**
     * Todo: Add lateness threshold disabler
     *
     * Enable the current view to forced to slave based on a enslaving ratio
     * typical views: boards, summaries, any minute-term static data, potentially any non-refreshing non-post-update
     * views
     *
     * @return void
     * @throws Exception
     */
    public static function enableSlave(): void
    {
        // Enslaved views are supposably session stallers so close session preventively
        CSessionHandler::writeClose();

        if (rand(0, 100) < CAppUI::conf("enslaving_ratio")) {
            self::enforceSlave(false);

            return;
        }
    }

    /**
     * Disable current view reroute to slave SQL
     *
     * @return void
     */
    public static function disableSlave(): void
    {
        if (!self::$slavestate) {
            return;
        }
        self::$slavestate = false;
        self::rerouteStdDS();
    }

    /**
     * Actual rerouting from std datasource to readonly slave datasource
     *
     * @return void
     */
    private static function rerouteStdDS(): void
    {
        foreach (CStoredObject::$spec as $_spec) {
            if ($_spec->dsn === "std") {
                $_spec->init();
            }
        }
    }

    /**
     * Start session if not opened and throw a warning
     *
     * @return void
     */
    private static function startSession(): void
    {
        if (!CSessionHandler::isOpen()) {
            if (self::$session_closed_warning) {
                trigger_error(
                    "Session is closed, setting parameters in session should be done before calling CView::checkin()",
                    E_USER_WARNING
                );
            }

            // Set-Cookie hack when closing et reopening session a couple of times
            $session_cookie = ini_get('session.use_cookies');
            ini_set('session.use_cookies', '0');

            CSessionHandler::start();

            ini_set('session.use_cookies', $session_cookie);
        }
    }

    /**
     * Reset CView parameters and return values before reset
     *
     * @return array $return Return all CView params for restoration
     */
    public static function reset(): array
    {
        $values                           = [];
        $values['checkedin']              = self::$checkedin;
        $values['params']                 = self::$params;
        $values['props']                  = self::$props;
        $values['session_closed_warning'] = self::$session_closed_warning;

        self::$checkedin              = false;
        self::$params                 = new stdClass();
        self::$props                  = [];
        self::$session_closed_warning = false;

        return $values;
    }

    /**
     * Restore CView parameters
     *
     * @param array $values Array containing values to restore
     *
     * @return void
     */
    public static function restore(array $values): void
    {
        self::$checkedin              = $values['checkedin'];
        self::$params                 = $values['params'];
        self::$props                  = $values['props'];
        self::$session_closed_warning = $values['session_closed_warning'];
    }

}

CView::$params = new \stdClass();
