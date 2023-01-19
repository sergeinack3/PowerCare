<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Le chargement des droits se fait sur le module "parent"

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\System\CExchangeSource;

global $m;

$path = CAppUI::conf("$m gnupg_path");
$path = $path ? $path : "~";
$home = exec("cd $path && pwd") . "/.gnupg";

$user_apache = exec('whoami');
// Check /root is writable
$writable = is_writable($home);

$source_name = isset($source_name) ? $source_name : $m;

// Source
$source = CExchangeSource::get($source_name, null, true, null, false);

// Source rescue
$source_rescue = CExchangeSource::get("$source_name-rescue", null, true, null, false);

// Création du template
$smarty = new CSmartyDP("modules/dPurgences");

$smarty->assign("user_apache", $user_apache);
$smarty->assign("home", $home);
$smarty->assign("path", $path);
$smarty->assign("writable", $writable);
$smarty->assign("source", $source);
$smarty->assign("source_rescue", $source_rescue);

// source lits chauds
if (isset($source_warm_bed)) {
  $smarty->assign("source_warm_bed", $source_warm_bed);
}

$smarty->display("Config_RPU_Sender");
