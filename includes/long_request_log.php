<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbServer;
use Ox\Core\Redis\CRedisClient;
use Ox\Core\CSQLDataSource;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Mediboard\System\AccessLog\CAccessLog;
use Ox\Mediboard\System\CLongRequestLog;
use Ox\Mediboard\System\CModuleAction;

if (CApp::isReadonly() || !CAppUI::conf("log_access") || !CAccessLog::$_current) {
    return;
}

$human_long_request_level = CAppUI::conf("human_long_request_level");
$bot_long_request_level   = CAppUI::conf("bot_long_request_level");

if (!$human_long_request_level && !$bot_long_request_level) {
    return;
}

$duration = CApp::$performance["genere"];

// TODO [public] Revert lors de la restauration du publique
$public = (CApp::getInstance()->isPublic() || !CAppUI::$user);
$bot    = ($public) ? false : CAppUI::$user->isRobot();

// Determine the log_level to apply
$long_request_log_level = false;
if ($bot && $bot_long_request_level) {
    $long_request_log_level = $bot_long_request_level;
} elseif (!$bot && $human_long_request_level) {
    // 'Public' is considered human
    $long_request_log_level = $human_long_request_level;
}

if (!$long_request_log_level) {
    return;
}

$long_request_whitelist   = explode("\n", CAppUI::conf("long_request_whitelist"));
$current_module_action_id = CAccessLog::$_current->module_action_id;
$isWhitelisted            = false;
foreach ($long_request_whitelist as $module_action) {
    if (
    preg_match(
        "/^(?P<module>[a-zA-Z0-9]+)\s(?P<action>[a-zA-Z0-9_]+)\s(?P<probability>\d+)/",
        trim($module_action),
        $matches
    )
    ) {
        $module_action_id = CModuleAction::getID($matches['module'], $matches['action']);

        if ($module_action_id === $current_module_action_id) {
            $probability   = $matches['probability'];
            $isWhitelisted = true;
            break;
        }
    }
}

// If request is too slow
if ($duration > $long_request_log_level || ($isWhitelisted && (mt_rand(1, $probability) === 1))) {
    // We store it
    $long_request_log                   = new CLongRequestLog();
    $long_request_log->datetime_start   = CMbDT::dateTime("-" . round($duration) . " SECONDS");
    $long_request_log->datetime_end     = CMbDT::dateTime();
    $long_request_log->duration         = $duration;
    $long_request_log->server_addr      = CMbServer::getServerVar('SERVER_ADDR');
    $long_request_log->user_id          = ($public) ? null : CAppUI::$user->_id;
    $long_request_log->session_id       = ($public) ? null : CSessionHandler::getSessionId();
    $long_request_log->module_action_id = $current_module_action_id;

    // GET and POST params
    $long_request_log->_query_params_get  = $_GET;
    $long_request_log->_query_params_post = $_POST;

    $session = CSessionHandler::getSessionDatas();

    unset($session['AppUI']);
    unset($session['dPcompteRendu']['templateManager']);

    // SESSION params
    $long_request_log->_session_data = $session;

    // Performance data
    $long_request_log->_query_performance = CApp::$performance;

    // Query report
    CSQLDataSource::buildReport(20);
    CRedisClient::buildReport(20);

    $long_request_log->_query_report = [
        'sql'   => CSQLDataSource::$report_data,
        'nosql' => CRedisClient::$report_data,
    ];

    // Unique Request ID
    $long_request_log->requestUID = CApp::getRequestUID();

    if ($msg = $long_request_log->store()) {
        trigger_error($msg, E_USER_WARNING);
    }
}
