<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CSocketBasedServer;
use Ox\Core\CValue;

$process_id = CValue::get("process_id");
$uid        = CValue::get("uid");

$tmp_dir    = CSocketBasedServer::getTmpDir();
$pid_files  = glob("$tmp_dir/pid.*");

foreach($pid_files as $_file) {
  $_pid = substr($_file, strrpos($_file, ".") + 1);
  if ($process_id != $_pid) {
    continue;
  }
  
  if (@unlink($_file) === true) {
    CAppUI::displayAjaxMsg("Le fichier 'pid.$process_id' a été supprimé");
    return;
  } 
}

CAppUI::displayAjaxMsg("Le fichier 'pid.$process_id' n'a pas pu être supprimé", UI_MSG_ERROR);

$processes = CSocketBasedServer::getPsStatus();
if (!array_key_exists($process_id, $processes)) {
  return;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("process_id", $process_id);
$smarty->assign("uid"       , $uid);
$smarty->assign("_process"  , $processes[$process_id]);
$smarty->display("inc_server_socket.tpl"); 
    

