<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Webservices\CSourceSOAP;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CExchangeSource;

CCanDo::checkAdmin();

$pratId    = CValue::getOrSession("object_id");
$pratId400 = CValue::getOrSession("id400");
$date      = CMbDT::dateTime();

//Création d'un nouvel id400 pour le laboratoire
$new_idex = new CIdSante400();

$prat = new CMediusers();
$listPrat = $prat->loadPraticiens();

$remote_name = CAppUI::gconf("dPlabo CCatalogueLabo remote_name");

$idex = new CIdSante400();
$idex->object_class = "CMediusers";
$idex->tag = $remote_name;

$idexs = $idex->loadMatchingList();

foreach ($idexs as $_idex) {
  $_idex->loadRefs();
}

$prescriptionlabo_source = CExchangeSource::get("prescriptionlabo", CSourceFTP::TYPE, true, null, false);
$get_id_prescriptionlabo_source = CExchangeSource::get("get_id_prescriptionlabo", CSourceSOAP::TYPE, true, null, false);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("prescriptionlabo_source" , $prescriptionlabo_source);
$smarty->assign("get_id_prescriptionlabo_source" , $get_id_prescriptionlabo_source);
$smarty->assign("listPrat", $listPrat);
$smarty->assign("date", $date);
$smarty->assign("remote_name", $remote_name);
$smarty->assign("newId400", $new_idex);
$smarty->assign("list_idSante400", $idexs);

$smarty->display("configure");
