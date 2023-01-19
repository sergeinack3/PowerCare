<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CFirstNameAssociativeSex;
use Ox\Mediboard\System\CObjectPseudonymiser;
use Ox\Mediboard\System\CSourceSMTP;
use Ox\Mediboard\System\CUserLog;

CCanDo::checkAdmin();

// Chargement des fuseaux horaires
$zones      = timezone_identifiers_list();
$continents = array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');
$timezones  = array();
foreach ($zones as $zone) {
  $parts = explode('/', $zone, 2); // 0 => Continent, 1 => City

  // Only use "friendly" continent names
  if (isset($parts[1]) && in_array($parts[0], $continents)) {
    $timezones[$parts[0]][$zone] = str_replace('_', ' ', $parts[1]); // Creates array(DateTimeZone => 'Friendly name')
  }
}

$firstname_tbl_installed = (CFirstNameAssociativeSex::countData() == 0) ? false : true;

// Source SMTP
$message_smtp = CExchangeSource::get("system-message", CSourceSMTP::TYPE, true, null, false);

// Stats migration user_log > user_action
$stat_migration_log_to_action = null;
if (CAppUI::conf("activer_migration_log_to_action")) {
  $log = new CUserLog();
  $stat_migration_log_to_action = $log->statMigrationLogToAction();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("timezones", $timezones);
$smarty->assign("message_smtp", $message_smtp);
$smarty->assign("pseudonymise_classes", CObjectPseudonymiser::$classes_handled);
$smarty->assign("firstname_tbl_installed", $firstname_tbl_installed);
$smarty->assign("conf_pat_tel", CAppUI::gconf("dPpatients CPatient tel_patient_mandatory"));
$smarty->assign("conf_pat_addr", CAppUI::gconf("dPpatients CPatient addr_patient_mandatory"));
$smarty->assign("stat_migration_log_to_action", $stat_migration_log_to_action);
$smarty->display("configure.tpl");
