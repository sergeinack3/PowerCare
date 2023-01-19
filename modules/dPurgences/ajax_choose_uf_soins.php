<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$callback    = CView::get("callback", "str");
$sejour_id   = CView::get("sejour_id", "ref class|CSejour");
$UHCD        = CView::get("UHCD", "bool");
$type_sejour = CView::get("type_sejour", "str");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$type_conf  = ($UHCD ? "UHCD" : "ATU");

$uf_soins_id    = CAppUI::gconf("dPurgences CRPU uf_soins_$type_conf");
$uf_medicale_id = CAppUI::gconf("dPurgences CRPU uf_medicale_$type_conf");
$charge_id      = CAppUI::gconf("dPurgences CRPU charge_$type_conf");

if ($charge_id) {
    $sejour->charge_id = $charge_id;
}

$curr_aff = $sejour->loadRefCurrAffectation();
if (!$curr_aff->_id) {
    $sejour->loadRefsAffectations();
    $curr_aff = $sejour->_ref_last_affectation;
}

$curr_aff->loadRefLit();
$curr_aff->loadRefService();

if ($uf_soins_id) {
    $curr_aff->uf_soins_id = $uf_soins_id;
}

if ($uf_medicale_id) {
    $curr_aff->uf_medicale_id = $uf_medicale_id;
}

if (CAppUI::gconf("dPurgences CRPU mode_entree_provenance_mutation")) {
    $curr_aff->mode_entree = "8";
    $curr_aff->provenance  = "5";

    if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree")) {
        $mode_entree           = new CModeEntreeSejour();
        $mode_entree->code     = "85";
        $mode_entree->group_id = $sejour->group_id;
        $mode_entree->actif    = 1;
        $mode_entree->loadMatchingObject();
        $curr_aff->mode_entree_id = $mode_entree->_id;
    }
}

$curr_aff->loadRefModeEntree();

$services = $UHCD ? CService::loadServicesUHCD() : CService::loadServicesUrgence();

if (!$curr_aff->_ref_service->{$UHCD ? "uhcd" : "urgence"}) {
    $curr_aff->service_id = "";
    $curr_aff->lit_id     = "";
    $curr_aff->_ref_lit   = new CLit();
}

if (!$curr_aff->service_id && count($services) === 1) {
    $first_service        = reset($services);
    $curr_aff->service_id = $first_service->_id;
}

$curr_aff->loadRefService();

$date_aff = CMbDT::dateTime();

if ($date_aff > $sejour->sortie || !$curr_aff->_id) {
    $date_aff = $sejour->entree;
    if ($curr_aff->_id) {
        $date_aff = CMbDT::dateTime("+10 seconds", $curr_aff->entree);
    }
}

$smarty = new CSmartyDP();

$smarty->assign("UHCD", $UHCD);
$smarty->assign("callback", $callback);
$smarty->assign("ufs", CUniteFonctionnelle::getUFs($sejour));
$smarty->assign("cpi_list", CChargePriceIndicator::getList($type_sejour));
$smarty->assign("list_mode_entree", CModeEntreeSejour::listModeEntree());
$smarty->assign("sejour", $sejour);
$smarty->assign("curr_aff", $curr_aff);
$smarty->assign("date_aff", $date_aff);
$smarty->assign("services", $services);

$smarty->display("inc_choose_uf_soins");
