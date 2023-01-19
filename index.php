<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Autoload\CAutoloadAlias;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CConfigController;
use Ox\Core\CDevtools;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CError;
use Ox\Core\Chronometer;
use Ox\Core\CMbConfig;
use Ox\Core\CMbDT;
use Ox\Core\CMbPerformance;
use Ox\Core\CMbRange;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Module\CModule;
use Ox\Core\Mutex\CMbMutex;
use Ox\Core\OAuth2\OIDC\PSC\Client;
use Ox\Core\Redis\CRedisClient;
use Ox\Core\ResourceLoaders\CHTMLResourceLoader;
use Ox\Core\Security\Csrf\AntiCsrf;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Core\Sessions\CSessionManager;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\Admin\Rgpd\CRGPDException;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CConfiguration;
use Ox\Mediboard\System\Controllers\Legacy\CMainController;
use Ox\Mediboard\System\Cron\CCronJobLog;
use Ox\Mediboard\System\CTabHit;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Legacy Front Controller
 */

// Autoload vendor
/** @var Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__ . "/vendor/autoload.php";

CMbPerformance::start();

$app = CApp::getInstance();

try {
    $app->checkMandatoryFiles();
} catch (Exception $e) {
    die($e->getMessage());
}

try {
    $dPconfig = $app->includeConfigs();
} catch (Exception $e) {
    die($e->getMessage());
}

$rootName = basename($dPconfig["root_dir"]);
date_default_timezone_set($dPconfig["timezone"]);

register_shutdown_function([CApp::class, 'handleShutdownCallbacks']);

CError::init();

Cache::init(CApp::getAppIdentifier());

CAppUI::init();
CClassMap::init();

// Autoload alias (use classmap)
CAutoloadAlias::register();

CApp::registerShutdown([CMbMutex::class, 'releaseMutexes'], CApp::MUTEX_PRIORITY);

if ($dPconfig['log_all_queries']) {
    CSQLDataSource::$log = true;
    CRedisClient::$log   = true;
}

// Offline
if ($app->isOffline()) {
    $app->sendOfflineResponseAndDie(CApp::MSG_OFFLINE_MAINTENANCE);
}

if (!$app->isDatabaseAccessible()) {
    $app->sendOfflineResponseAndDie(CApp::MSG_OFFLINE_DATABASE);
}

// Register check in peace function
CApp::registerShutdown([CApp::class, 'checkPeace'], CApp::PEACE_PRIORITY);

// Include config in DB
if (CAppUI::conf("config_db")) {
    CMbConfig::loadValuesFromDB();
}

// Boot Symfony dotenv
if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(__DIR__ . '/.env');
}

// Load module before require session.php
// Previously code out of class CModule load by CSessionHandler::start() > CStoredObject->__wakeup()
CModule::loadModules();
CMbPerformance::mark("modules");

// Init shared memory, must be after DB init and Modules loading
Cache::initDistributed();

CMbPerformance::mark("init");

// Session
$session_manager = CSessionManager::get();
$session_manager->init();
CMbPerformance::mark("session");

// Start chrono (after session_start as it may be locked by another request)
CApp::$chrono       = new Chronometer();
CApp::$chrono->main = true;
CApp::$chrono->start();

$do_login = false;

if ($cron_job_log_id = CValue::get('execute_cron_log_id')) {
    CCronJobLog::setCronJobLogId((int) $cron_job_log_id);
}

// Load default preferences if not logged in
if (!CAppUI::$instance->user_id) {
    CAppUI::loadPrefs();
}

CAppUI::$session_no_revive = (bool)CValue::get('session_no_revive');

// Update session lifetime
CSessionHandler::setUserDefinedLifetime();

// If the user uses a token, his session should not be reset, but only redirected
$token_hash = CValue::get("token");

if ($possibly_token_hash = CViewAccessToken::getShortURLTokenHash()) {
    $token_hash = $possibly_token_hash;
}

if ($token_hash) {
    $token = CViewAccessToken::getByHash($token_hash);
    // If the user is already logged in (in a normal session), keep his session, but use the params
    if (CAppUI::$instance->user_id && !CAppUI::$token_session) {
        if ($token->isValid() && CAppUI::$instance->user_id == $token->user_id) {
            $token->useIt();
            CAppUI::redirect($token->getQueryString());
            CApp::rip();
        }
    } else {
        $do_login = true;
    }
}

// We force the dialog view if in a token session
// !dialog because of fallback processes
if ((CAppUI::$token_session || $do_login) && !CValue::request('dialog')) {
    $dialog = 1;
}

// Check ldap_guid or sining token
if (CValue::get("ldap_guid") || $do_login) {
    $_REQUEST["login"] = 1;
}

$krb_user      = ($_SERVER['REMOTE_USER']) ?? null;
$krb_auth_type = ($_SERVER['AUTH_TYPE']) ?? null;
if (!CAppUI::$instance->user_id && $krb_user && ($krb_auth_type === 'Negotiate')) {
    $_REQUEST['login'] = 1;
}

$psc = $_REQUEST['psc'] ?? null;
if (!CAppUI::$instance->user_id && $psc) {
    $_REQUEST['login'] = 1;
}

$fc = $_REQUEST['fc'] ?? null;
if (!CAppUI::$instance->user_id && $fc) {
    $_REQUEST['login'] = 1;
}

// Register configuration
CConfiguration::registerAllConfiguration();

CMbPerformance::mark("config");

// check if the user is trying to log in
if (isset($_REQUEST["login"])) {
    $login_action = $_REQUEST["login"];

    // login with "login=user:password"
    if (strpos($login_action, ":") !== false) {
        [$_REQUEST["username"], $_REQUEST["password"]] = explode(":", $login_action, 2);
    }

    include __DIR__ . "/locales/core.php";
    if (null == $ok = CAppUI::login()) {
        CAppUI::$instance->user_id = null;
    }

    // Login OK redirection for popup authentication
    $redirect = CValue::request("redirect");
    $dialog   = CValue::request("dialog");
    parse_str($redirect, $parsed_redirect);
    if ($ok && $dialog && isset($parsed_redirect["login_info"])) {
        $redirect = "m=system&a=login_ok&dialog=1";
    }

    // Actual redirection
    if ($redirect) {
        CAppUI::redirect($redirect);
    }

    // Empty post data only if we login by POST (with the login page)
    if (isset($_POST["login"])) {
        CApp::emptyPostData();
    }
}

CAppUI::updateUserAuthExpirationDatetime();

CMbPerformance::mark("auth");

// Default view
$index = "index";

// Don't output anything. Usefull for fileviewers, ajax requests, exports, etc.
$suppressHeaders = CValue::request("suppressHeaders");

// WSDL if often stated as final with no value (&wsdl) wrt client compat
$wsdl = CValue::request("wsdl");
if (isset($wsdl)) {
    $suppressHeaders = 1;
    $index           = $wsdl;
    $wsdl            = 1;
}

// Info output for view reflexion purposes
if ($info = CValue::request("info")) {
    $index = $info;
    $info  = 1;
}

// Output the charset header in case of an ajax request
if ($ajax = CValue::request("ajax")) {
    $suppressHeaders = 1;
    $index           = $ajax;
    $ajax            = 1;
}

// Raw output for export purposes
if ($raw = CValue::request("raw")) {
    $suppressHeaders = 1;
    $index           = $raw;
}

// Check if we are in the dialog mode
if ($dialog = CValue::request("dialog")) {
    $index  = $dialog;
    $dialog = 1;
}
CAppUI::$dialog = &$dialog;

// clear out main url parameters
$m = $a = $g = $f = "";

CMbPerformance::mark("input");

// Locale
$locale_info = CAppUI::loadCoreLocales();

// Character set
if (!$suppressHeaders || $ajax) {
    header("Content-type: text/html;charset=" . CApp::$encoding);
}

CMbPerformance::mark("locales");

// HTTP headers
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // always modified
header("Cache-Control: no-cache, no-store, must-revalidate");  // HTTP/1.1
header("Pragma: no-cache");  // HTTP/1.0
header("X-UA-Compatible: IE=edge"); // Force IE document mode
header("X-Request-ID: " . CApp::getRequestUID()); // Correlates HTTP requests between a client and server

// Show errors to admin
ini_set("display_errors", CAppUI::pref("INFOSYSTEM"));

CMbPerformance::mark("headers");


CAppUI::$user = new CMediusers();
if (CAppUI::$user->isInstalled()) {
    CAppUI::$user->load(CAppUI::$instance->user_id);
    CAppUI::$user->getBasicInfo();
    CAppUI::$instance->_ref_user =& CAppUI::$user;

    // Offline mode for non-admins
    if ($dPconfig["offline_non_admin"] && CAppUI::$user->_id != 0 && !CAppUI::$user->isAdmin()) {
        $app->sendOfflineResponseAndDie(CApp::MSG_OFFLINE_MAINTENANCE);
    }

    CApp::$is_robot = CAppUI::$user->isRobot();
}

// Load User Perms, previously load by code out of class CPermModule & CPermObject
CPermModule::loadUserPerms();
CPermObject::loadUserPerms();

CMbPerformance::mark("user");

// Init output filter
CHTMLResourceLoader::initOutput(CValue::get("_aio"));

CApp::notify("BeforeMain");


/****************************************************************
 * START Main URL dispatcher
 ***************************************************************/

// Get the user's style
$uistyle = CAppUI::MEDIBOARD_EXT_THEME;
$main    = new CMainController();

// Check if we are logged in
if (!CAppUI::$instance->user_id) {
    $_SESSION["locked"] = null;

    // HTTP 403 Forbidden header when RAW response expected
    if ($suppressHeaders && !$ajax) {
        header("HTTP/1.0 403 Forbidden");
        CApp::rip();
    }

    // Ajax login alert
    if ($ajax) {
        header('HTTP/1.1 401 Unauthorized', true, 401);
        $main->ajaxErrors();
    } else {
        $main->login();
    }

    // Destroy the current session and output login page
    CSessionHandler::end(true);
    CApp::rip();
}

// init devtools
if (CApp::getRequestHeaders(CDevtools::REQUEST_HEADER) !== null) {
    CDevtools::init();
}

$tab = 1;

$m            = $m_get = CValue::get("m");
$post_request = $_SERVER['REQUEST_METHOD'] == 'POST';

if ($post_request) {
    $m = CValue::post("m") ?: $m;
}

$m = CAppUI::checkFileName($m);
if (null == $m) {
    $m     = CPermModule::getFirstVisibleModule();
    $parts = explode("-", CAppUI::pref("DEFMODULE"), 2);

    $pref_module = $parts[0];
    if ($pref_module && CPermModule::getViewModule(CModule::getInstalled($pref_module)->mod_id, PERM_READ)) {
        $m = $pref_module;
    }

    if (count($parts) == 2) {
        $tab = $parts[1];
        CValue::setSession("tab", $tab);
    }
}

// Still no target module
if (null == $m) {
    CAppUI::accessDenied();
}

if (null == $module = CModule::getInstalled($m)) {
    // dP remover super hack
    if (null == $module = CModule::getInstalled("dP$m")) {
        CAppUI::redirect("m=system&a=module_missing&mod=$m");
    }
    $m = "dP$m";
}

// Get current module permissions
// these can be further modified by the included action files
$can = $module->canDo();

$a      = CAppUI::checkFileName(CValue::get("a", $index));
$dosql  = CAppUI::checkFileName(CValue::post("dosql", ""));
$class  = CAppUI::checkFileName(CValue::post("@class", ""));
$config = CAppUI::checkFileName(CValue::post("@config", ""));

$tab = CValue::get('tab');
if ($a === 'index' && ($session_tab = CValue::getOrSession('tab', $tab)) !== 'chpwd') {
    $tab = $session_tab;
}

// set the group in use, put the user group if not allowed
$g          = CValue::getOrSessionAbs("g", CAppUI::$instance->user_group);
$indexGroup = CGroups::get($g);
if ($indexGroup && !$indexGroup->getPerm(PERM_READ)) {
    $g = CAppUI::$instance->user_group;
    CValue::setSessionAbs("g", $g);
}

// Check whether the password is strong enough
// If account is not a robot, nor linked to the LDAP
$user = CAppUI::$user;

// set the function in use
$f = CValue::getOrSessionAbs("f", $user->function_id);

// Force the current group system date if configured
CMbDT::setSystemDate(CAppUI::gconf('system General system_date', $g));

AntiCsrf::init();

// do some db work if dosql is set
if ($dosql) {
    // dP remover super hack
    if (!CModule::getInstalled($m)) {
        if (!CModule::getInstalled("dP$m")) {
            CAppUI::redirect("m=system&a=module_missing&mod=$m");
        }
        $m = "dP$m";
    }

    CSQLDataSource::$log = true;
    CRedisClient::$log   = true;

    // controller in controllers/ directory
    if (is_file("./modules/$m/controllers/$dosql.php")) {
        include "./modules/$m/controllers/$dosql.php";
    } elseif ($controller = $module->matchLegacyController($dosql)) {
        $controller->$dosql();
    }

    CSQLDataSource::$log = false;
    CRedisClient::$log   = false;
}

// Permissions checked on POST $m, but we redirect to GET $m
if ($post_request && $m_get && $m != $m_get && $m != "dP$m_get") {
    $m = $m_get;
}

if (!CAppUI::isPatient()) {
    CSQLDataSource::$log = true;
    CRedisClient::$log   = true;

    if ($class) {
        $do = new CDoObjectAddEdit($class);
        $do->doIt();
    }

    if ($config) {
        $config = new CConfigController($config);
        $config->updateConfigs();
    }

    CSQLDataSource::$log = false;
    CRedisClient::$log   = false;
}

// Load module tabs (AbstractTabsRegister)
$module->registerTabs();

if (!$a || $a === "index") {
    $tab = $module->getValidTab($tab);
}

if (!$suppressHeaders) {
    // Check if we have to register the hit
    // $a must be null or must be 'index' (it meen tab= is used)
    if ($tab && (!$a || $a === 'index') && CSQLDataSource::get('std')->hasTable('tab_hit')) {
        CTabHit::registerHit($module, $tab);
    }

    $main->header($m, $a, $tab);
}

// Check muters
if ($muters = CValue::get("muters")) {
    $muters = explode("-", $muters);
    if (count($muters) % 2 != 0) {
        trigger_error("Muters should come by min-max intervals time pairs", E_USER_WARNING);
    } else {
        $time_now = CMbDT::time();
        while (count($muters)) {
            $time_min = array_shift($muters);
            $time_max = array_shift($muters);
            if (CMbRange::in($time_now, $time_min, $time_max)) {
                CAppUI::stepMessage(UI_MSG_OK, "msg-common-system-muted", $time_now, $time_min, $time_max);

                return;
            }
        }
    }
}

CSQLDataSource::$log = true;
CRedisClient::$log   = true;

if (
    !$user->isSuperAdmin()
    && (!($m == "admin" && $tab == "chpwd") && !($m == "admin" && $dosql == "do_chpwd_aed"))
    && $user->_ref_user->canChangePassword()
) {
    if (
        CAppUI::$instance->weak_password
        && (!CAppUI::$instance->user_remote || CAppUI::conf("admin CUser apply_all_users"))
    ) {
        CAppUI::redirect("m=admin&tab=chpwd&forceChange=1");
        CAppUI::accessDenied();
    }

    // If we want to force user to periodically change password
    if (CAppUI::conf("admin CUser force_changing_password") || $user->mustChangePassword()) {
        // Need to change
        if ($user->mustChangePassword()) {
            CAppUI::redirect("m=admin&tab=chpwd&forceChange=1");
            CAppUI::accessDenied();
        }

        if (!$user->_ref_user->isLDAPLinked()) {
            if (
                CMbDT::dateTime(
                    "-" . CAppUI::conf("admin CUser password_life_duration")
                ) > $user->_ref_user->user_password_last_change
            ) {
                CAppUI::redirect("m=admin&tab=chpwd&forceChange=1&lifeDuration=1");
                CAppUI::accessDenied();
            }
        }
    }
}

// PSC Session check
Client::checkSession(CAppUI::$instance->oidc_tokens);

try {
    $rgpd_manager = new CRGPDManager($g);

    if (
        $rgpd_manager->isEnabledFor($user->_ref_user)
        && $rgpd_manager->canAskConsentFor($user->_ref_user)
        && !$rgpd_manager->checkConsentFor($user->_ref_user)
    ) {
        CUser::requireUserConsent();
    } else {
        // tabBox et inclusion du fichier demandé
        if ($tab !== null) {
            $module->showTabs();
        } else {
            $module->showAction();
        }
    }
} catch (CRGPDException $e) {
    CApp::log("GDPR: {$e->getMessage()}", null, LoggerLevels::LEVEL_DEBUG);

    // tabBox et inclusion du fichier demandé
    if ($tab !== null) {
        $module->showTabs();
    } else {
        $module->showAction();
    }
}

CSQLDataSource::$log = false;
CRedisClient::$log   = false;

CApp::$chrono->stop();

// Requests after CApp::preparePerformance will not be logged!
CApp::preparePerformance();

if (!CView::$checkedin) {
    $tab !== null ? $filename = $tab : $filename = $a;
    trigger_error("CView::checkin() has not been called in $filename.php");
}

// Unlocalized strings
if (!$suppressHeaders || $ajax) {
    $main->unlocalized();
}

// Inclusion du footer
if (!$suppressHeaders) {
    $main->footer();
}

// Ajax performance, messagerie
if ($ajax) {
    $main->ajaxErrors();
}

CView::disableSlave();

CApp::notify("AfterMain");

// Send timing data in HTTP header
CMbPerformance::end();

CMbPerformance::writeHeader();

// Output HTML
$aio_options = [
    "ignore_scripts" => CValue::get("_aio_ignore_scripts"),
];
CHTMLResourceLoader::output($aio_options);

CApp::rip();
