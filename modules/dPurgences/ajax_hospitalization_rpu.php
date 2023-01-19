<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkEdit();

$rpu_id   = CView::get("rpu_id", "ref class|CRPU");
$group_id = CView::get("group_id", "ref class|CGroups");

CView::checkin();

$number_tolerance = CAppUI::gconf("dPurgences CRPU search_visit_days_count");
$now              = CMbDT::dateTime();
$after            = CMbDT::dateTime("+ $number_tolerance DAY", $now);

$current_group = CGroups::get();

$rpu = new CRPU();
$rpu->load($rpu_id);

$sejour_rpu = $rpu->loadRefSejour();
$patient    = $sejour_rpu->loadRefPatient();

$sejour_rpu->type         = "comp";
$sejour_rpu->praticien_id = ""; // On oblige la sélection d'un praticien non urgentiste

if ($group_id) {
    $sejour_rpu->group_id = $group_id;
}
$collisions = $sejour_rpu->getCollisions();


$check_merge      = "";
$sejours_futur    = [];
$count_collision  = count($collisions);
$sejour_collision = "";

if ($count_collision == 1) {
    $sejour_collision = current($collisions);
    $sejour_collision->loadRefPraticien();

    try {
        $sejour_rpu->checkMerge($collisions);
        $check_merge = null;
    } catch (Throwable $t) {
        $check_merge = $t->getMessage();
    }
} else {
    if (!$count_collision) {
        $sejour = new CSejour();
        $where  = [
            "entree"     => "BETWEEN '$now' AND '$after'",
            "sejour_id"  => "!= '$sejour->_id'",
            "patient_id" => "= '$patient->_id'",
            "annule"     => "= '0'",
        ];

        if ($group_id) {
            $where["group_id"] = "= '$group_id'";
        }

        /** @var CSejour[] $sejours_futur */
        $sejours_futur = $sejour->loadList($where, "entree DESC", null, "type");
        foreach ($sejours_futur as $_sejour_futur) {
            $_sejour_futur->loadRefPraticien()->loadRefFunction();
        }
    }
}

$other_group = false;
$services    = [];
if ($group_id && $group_id != CGroups::get()->_id) {
    $other_group = true;

    $service  = new CService();
    $services = $service->loadList(["group_id" => "= '$group_id'"], "nom");

    $sejour_rpu->mode_entree = "7";
}

$sejour_rpu->loadRefPraticien();

$required_uf_soins = CAppUI::conf(
    "dPplanningOp CSejour required_uf_soins",
    "CGroups-" . ($group_id ?: $current_group->_id)
);
$required_uf_med   = CAppUI::conf(
    "dPplanningOp CSejour required_uf_med",
    "CGroups-" . ($group_id ?: $current_group->_id)
);

$ufs = CUniteFonctionnelle::getUFs(
    $sejour_rpu,
    $group_id ?: null,
    ["type_sejour" => "IS NULL OR type_sejour " . CSQLDataSource::prepareIn(["comp", "ambu"])]
);

$change_group = CAppUI::gconf("dPurgences CRPU change_group");

$etablissements = [];

if ($change_group) {
    $etablissements = CMediusers::loadEtablissements(PERM_READ);
}

$smarty = new CSmartyDP();

$smarty->assign("count_collision", $count_collision);
$smarty->assign("rpu", $rpu);
$smarty->assign("sejour", $sejour_rpu);
$smarty->assign("sejours_futur", $sejours_futur);
$smarty->assign("sejour_collision", $sejour_collision);
$smarty->assign("check_merge", $check_merge);
$smarty->assign("required_uf_soins", $required_uf_soins);
$smarty->assign("required_uf_med", $required_uf_med);
$smarty->assign("ufs", $ufs);
$smarty->assign("change_group", $change_group);
$smarty->assign("etablissements", $etablissements);
$smarty->assign("other_group", $other_group);
$smarty->assign("group_id", $group_id);
$smarty->assign("services", $services);

$smarty->display("inc_hospitalization_rpu");
