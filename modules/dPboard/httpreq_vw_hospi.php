<?php

/**
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admissions\CSejourLoader;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\OxLaboClient\OxLaboClientHandler;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;

/**
 * dPboard
 */
CCanDo::checkRead();

$chirSel     = CView::get("chirSel", "ref class|CMediusers", true);
$date        = CView::get("date", "date default|now");
$function_id = CView::get("functionSel", "ref class|CFunctions", true);

CView::checkin();

$praticien = new CMediusers();
$praticien->load($chirSel);

// Chargement de l'utilisateur courant
$userCourant = CMediusers::get();

// Variables nescessaires
$viewMode = CValue::get('viewMode');
$viewMode ? $view = 'day' : $view = 'instant';
$mode             = CValue::get('mode', $view);
$only_non_checked = CValue::get("only_non_checked", 0);

$alert_handler = HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler');

$board = true;

$where = [];
$ljoin = [];

if ($chirSel) {
    $wherePrat = $praticien->getUserSQLClause();
    if ($praticien->isAnesth()) {
        $ljoin["operations"] = "operations.sejour_id = sejour.sejour_id";
        $ljoin["plagesop"]   = "operations.plageop_id = plagesop.plageop_id";
        $where[]             =
            "operations.anesth_id $wherePrat OR (operations.anesth_id IS NULL AND plagesop.anesth_id $wherePrat)";
    } else {
        $where["sejour.praticien_id"] = $wherePrat;
    }
}
$where["sejour.entree"] = "<= '$date 23:59:59'";
$where["sejour.sortie"] = ">= '$date 00:00:00'";
$where["sejour.annule"] = "= '0'";

if ($function_id) {
    $ljoin["users_mediboard"]             = "users_mediboard.user_id = sejour.praticien_id";
    $where["users_mediboard.function_id"] = " = '$function_id'";
}

$sejour = new CSejour();
/** @var CSejour[] */
$sejours = $sejour->loadGroupList($where, null, null, null, $ljoin);

if (CAppUI::gconf("dPplanningOp CSejour use_prat_aff") && (($chirSel && !$praticien->isAnesth()) || $function_id)) {
    $ljoin["affectation"] = "affectation.sejour_id = sejour.sejour_id";

    if ($chirSel && !$praticien->isAnesth()) {
        unset($where["sejour.praticien_id"]);
        $where["affectation.praticien_id"] = $wherePrat;
    } elseif ($function_id) {
        unset($ljoin["users_mediboard"]);
        $ljoin["users_mediboard"] = "users_mediboard.user_id = affectation.praticien_id";
    }

    $sejours += $sejour->loadGroupList($where, null, null, "sejour.sejour_id", $ljoin);
}

$sejours = CSejourLoader::loadSejoursForSejoursView($sejours, [$praticien], $date, $only_non_checked);


if ($chirSel) {
    // Si un praticien est sélectionné, on imprimera ses interventions
    $print_content_class = "CMediusers";
    $print_content_id    = $chirSel;
} elseif ($function_id) {
    // Sinon, si un cabinet est sélectionnée, on imprimera les interventions du cabinet
    $print_content_class = "CFunctions";
    $print_content_id    = $function_id;
}
$function = new CFunctions();
if ($function_id) {
    $function->load($function_id);
}

//Chargement des alertes OxLabo
$source_labo = CExchangeSource::get(
    "OxLabo" . CGroups::loadCurrent()->_id,
    CSourceHTTP::TYPE,
    false,
    "OxLaboExchange",
    false
);
$labo_alert_by_nda     = [];
$new_labo_alert_by_nda = [];
$id_sejours = [];
if (CModule::getActive("oxLaboClient") && $source_labo->active) {
    $labo_handler          = new OxLaboClientHandler();
    $labo_alert_by_nda     = $labo_handler->getAlerteAnormalForSejours($sejours);
    $new_labo_alert_by_nda = $labo_handler->getAlertNewResultForSejours($sejours);
    foreach ($sejours as $_sejour) {
        $id_sejours[] = $_sejour->_id;
    }
}

// Création du template
//$smarty = new CSmartyDP();
$smarty = new CSmartyDP("modules/soins");

$smarty->assign("date", $date);
$smarty->assign("praticien", $praticien);
$smarty->assign("sejours", $sejours);
$smarty->assign("board", $board);
$smarty->assign("service", new CService());
$smarty->assign("service_id", null);
$smarty->assign("etats_patient", []);
$smarty->assign("show_affectation", false);
$smarty->assign("function", $function);
$smarty->assign("sejour_id", null);
$smarty->assign("show_full_affectation", true);
$smarty->assign("only_non_checked", false);
$smarty->assign("print", false);
$smarty->assign("_sejour", new CSejour());
$smarty->assign('ecap', false);
$smarty->assign('mode', $mode);
$smarty->assign('services_selected', []);
$smarty->assign("discipline", new CDiscipline());
$smarty->assign("lite_view", true);
$smarty->assign("print_content_class", $print_content_class);
$smarty->assign("print_content_id", $print_content_id);
$smarty->assign("allow_edit_cleanup", 1);


$smarty->assign("my_patient", false);
$smarty->assign("count_my_patient", 0);
$smarty->assign("new_labo_alert_by_nda", $new_labo_alert_by_nda);
$smarty->assign("labo_alert_by_nda", $labo_alert_by_nda);
$smarty->assign("id_sejours", json_encode($id_sejours));
$smarty->assign("getSourceLabo", $source_labo->active);

$smarty->display("inc_list_sejours_global");
