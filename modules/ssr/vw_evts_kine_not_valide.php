<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ssr\CEvenementSSR;

CCanDo::checkRead();

global $m;
$kine_id = CView::get("kine_id", "ref class|CMediusers", true);
$date    = CView::get("date", "date", true);
$page    = CView::get("page", "num default|0");
CView::checkin();

$monday   = CMbDT::date("last monday", $date);
$group_id = CGroups::loadCurrent()->_id;

// Chargement des evenements de la semaine precedente qui n'ont pas encore ete validés
$ljoin                          = array();
$ljoin["sejour"]                = "sejour.sejour_id = evenement_ssr.sejour_id";
$where                          = array();
$where["evenement_ssr.realise"] = " = '0'";
$where["evenement_ssr.annule"]  = " = '0'";
$where[]                        = "sejour.sejour_id IS NOT NULL";
$where["sejour.type"]           = " = '$m'";
$where["sejour.group_id"]       = " = '$group_id'";
$where[]                        = "evenement_ssr.therapeute_id = '$kine_id' AND evenement_ssr.debut < '$monday 00:00:00'";
$evenement                      = new CEvenementSSR();
/* @var CEvenementSSR[] $list_evts */
$list_evts = $evenement->loadList($where, "debut desc", "$page, 25", "evenement_ssr.evenement_ssr_id", $ljoin);
$nb_evts   = $evenement->countList($where, null, $ljoin);

//Chargements en masse
$sejours = CStoredObject::massLoadFwdRef($list_evts, "sejour_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");
/* @var CEvenementSSR[] $list_evts */
foreach ($list_evts as $_evt) {
  if ($_evt->seance_collective_id) {
    $_evt->loadView();
  }
  $_evt->loadRefSejour()->loadRefPatient();
}

$therapeute = new CMediusers();
$therapeute->load($kine_id);
$therapeute->loadRefFunction();

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("list_evts", $list_evts);
$smarty->assign("nb_evts", $nb_evts);
$smarty->assign("page", $page);
$smarty->assign("therapeute", $therapeute);
$smarty->display("vw_evts_kine_not_valide");
