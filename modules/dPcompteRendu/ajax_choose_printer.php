<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Printing\CPrinter;
use Ox\Mediboard\Printing\CSourceLPR;
use Ox\Mediboard\Printing\CSourceSMB;

CCanDo::checkRead();

/**
 * Liste des imprimantes en réseau
 */
$mode_etiquette = CValue::get("mode_etiquette", 0);
$object_class   = CValue::get("object_class");
$object_id      = CValue::get("object_id");
$modele_etiquette_id = CValue::get("modele_etiquette_id");

$printer = new CPrinter();
$printer->function_id= CMediusers::get()->function_id;
$printers = $printer->loadMatchingList();

CStoredObject::massLoadFwdRef($printers, "object_id");

/** @var $printers CPrinter[] */
foreach ($printers as $_printer) {
  $_printer->loadTargetObject();
}

$source_lpr = new CSourceLPR();
$source_smb = new CSourceSMB();
$other_printers = $source_lpr->loadList();
$other_printers = array_merge($other_printers, $source_smb->loadList());

$smarty = new CSmartyDP();

$smarty->assign("mode_etiquette"     , $mode_etiquette);
$smarty->assign("printers"           , $printers);
$smarty->assign("other_printers"     , $other_printers);
$smarty->assign("object_class"       , $object_class);
$smarty->assign("object_id"          , $object_id);
$smarty->assign("modele_etiquette_id", $modele_etiquette_id);

$smarty->display("inc_choose_printer");