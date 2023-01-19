<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbServer;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;

CCanDo::checkAdmin();

$dsn         = CView::post("dsn", "str notNull");
$master_user = CView::post("host", "str notNull");

CView::checkin();

// Check params
if (!$dsn) {
  CAppUI::stepAjax("Aucun DSN sp�cifi�", UI_MSG_ERROR);
}

global $dPconfig;

if (!array_key_exists($dsn, $dPconfig["db"])) {
  CAppUI::stepAjax("Configuration pour le DSN '$dsn' inexistante", UI_MSG_ERROR);
}

$dsConfig =& $dPconfig["db"][$dsn];
$dbtype = $dsConfig["dbtype"];
if (strpos($dbtype, "mysql") === false) {
  CAppUI::stepAjax("Seules les DSN MySQL peuvent �tre cr��es par un acc�s administrateur", UI_MSG_ERROR);
}

// Substitute admin access
$user = $dsConfig["dbuser"];
$pass = $dsConfig["dbpass"];
$name = $dsConfig["dbname"];
$host = $dsConfig["dbhost"];

$dsConfig["dbuser"] = $master_user;
$dsConfig["dbpass"] = $master_pass;
$dsConfig["dbhost"] = $master_host;
$dsConfig["dbname"] = "";

$ds = @CSQLDataSource::get($dsn);
if (!$ds) {
  CAppUI::stepAjax("Connexion en tant qu'administrateur �chou�e", UI_MSG_ERROR);
}

CAppUI::stepAjax("Connexion en tant qu'administrateur r�ussie");

$client_host = "localhost";
if (!in_array($host, array("127.0.0.1", "localhost"))) {
  $client_host = CMbServer::getServerVar('SERVER_ADDR');
}

foreach ($ds->queriesForDSN($user, $pass, $name, $client_host) as $key => $query) {
  if (!$ds->exec($query)) {
    CAppUI::stepAjax("Requ�te '$key' �chou�e", UI_MSG_WARNING);
    continue;
  }
  
  CAppUI::stepAjax("Requ�te '$key' effectu�e");
}

CApp::rip();
