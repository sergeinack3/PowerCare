<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CRessourceCab;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$function_id = CView::getRefCheckRead("function_id", "ref class|CFunctions", true);
$type        = CView::get("type", "enum list|prats|ressources default|prat");
$date        = CView::get("date", "date default|now");
$ressources_ids = CValue::sessionAbs("planning_ressources_ids");

CView::checkin();

$available_ressources = [];

switch ($type) {
  default:
    $user = new CMediusers();
    ${$type} = $user->loadProfessionnelDeSanteByPref(PERM_READ, $function_id, null, true);
    break;
  case "ressources":
    $ressource = new CRessourceCab();
    $ressource->function_id = $function_id;
    $ressource->actif = 1;
    ${$type} = $ressource->loadMatchingList("libelle");

    $ressources = $ressource->loadMatchingList("libelle");
    CMbObject::massLoadBackRefs($ressources, "plages_cab");

    foreach ($ressources as $_ressource) {

      $_ressource->loadRefsPlages();
      CMbObject::massLoadBackRefs($_ressource->_ref_plages, "reservations");
      foreach ($_ressource->_ref_plages as $_plage) {
        if ($_plage->date === $date) {
          $available_ressources[] = $_ressource->_id;
        }
      }
    }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign($type, ${$type});
$smarty->assign("function_id", $function_id);
$smarty->assign("available_ressources", $available_ressources);
$smarty->assign("ressources_ids", $ressources_ids);

$smarty->display($type === "prats" ? "inc_filter_praticiens.tpl" : "inc_filter_ressources.tpl");
