<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$now                     = CMbDT::dateTime();
$now_time                = CMbDT::time();
$number_of_previous_days = CView::get("number_of_previous_days", "num default|1");
$yesterday               = CMbDT::dateTime("-$number_of_previous_days DAY");
$types                   = CView::get("types", "str default|consult");
$limit                   = intval(CView::get("limit", "num default|1"));
$cancel_sejours          = CView::get("cancel_sejours", "bool default|1");
$group_id                = CView::get("group_id", "ref class|CGroups");
$code_mode_sortie        = CView::get("code_mode_sortie", "str");
$exclude_services        = CView::get("exclude_services", "str");

CView::checkin();

if ($exclude_services !== "") {
    $exclude_services = explode('|', $exclude_services);
}
$types = explode("-", $types);
CMbArray::removeValue("", $types);

$sejour        = new CSejour();
$where = [];
$where["type"] = CSQLDataSource::prepareIn($types);
if (is_array($exclude_services)) {
    $where["service_id"] = CSQLDataSource::prepareNotIn($exclude_services) . ' OR service_id IS NULL';
}
$where['entree'] = "BETWEEN '$yesterday' AND '$now'";

if ($group_id) {
    $where["group_id"] = "= '$group_id'";
}

// Limite de 1
$limit = "0, $limit";

// Cloture des sejours passés
$where['entree_reelle'] = "IS NOT NULL";
$where['sortie_reelle'] = "IS NULL";
$order                  = "entree_reelle";
$sejours                = $sejour->loadList($where, $order, $limit);
CAppUI::stepAjax(count($sejours) . " séjours à clôturer", UI_MSG_OK);

$mode_sortie = new CModeSortieSejour();

if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie")) {
    $mode_sortie->code     = $code_mode_sortie != "" ? $code_mode_sortie : "80";
    $mode_sortie->actif    = 1;
    $mode_sortie->group_id = $group_id ?: CGroups::loadCurrent()->_id;
    $mode_sortie->loadMatchingObjectEsc("mode_sortie_sejour_id");
}

foreach ($sejours as $_sejour) {
    $sortie_reelle = $now;

    if (in_array($_sejour->type, ["ambu", "consult"])) {
        $sortie_reelle = CMbDT::date($_sejour->entree_reelle) . " " . $now_time;
    }

    $_sejour->mode_sortie    = "normal";
    $_sejour->mode_sortie_id = $mode_sortie->_id;
    $_sejour->sortie_reelle  = $sortie_reelle;
    $msg                     = $_sejour->store();
    CAppUI::stepAjax($msg ? "Séjour non clôturé" : "Séjour clôturé", $msg ? UI_MSG_WARNING : UI_MSG_OK);

    if ($msg) {
        CApp::log("Echec de la clôture du séjour $_sejour->_id : $msg");
    }
}

// Annulation des séjours sans entrée reelle ni sortie reelle
if ($cancel_sejours) {
    $where['entree_reelle'] = "IS NULL";
    $where['sortie_reelle'] = "IS NULL";
    $where['annule']        = "= '0'";
    $order                  = "entree_prevue";

    $sejours = $sejour->loadList($where, $order, $limit);

    CAppUI::stepAjax(count($sejours) . " séjours à annuler", UI_MSG_OK);

    foreach ($sejours as $_sejour) {
        $_sejour->annule = 1;
        $msg             = $_sejour->store();
        CAppUI::stepAjax($msg ? "Séjour non annulé" : "Séjour annulé", $msg ? UI_MSG_WARNING : UI_MSG_OK);
    }
}
