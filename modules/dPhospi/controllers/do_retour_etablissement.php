<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::check();

$affectation_id      = CView::post("affectation_id", "ref class|CAffectation");
$lit_id              = CView::post("lit_id", "ref class|CLit");
$service_id          = CView::post("service_id", "ref class|CService");
$affectation_perm_id = CView::post("affectation_perm_id", "ref class|CAffectation");
$callback            = CView::post("callback", "str");
$depart              = CView::post("depart", "bool default|0");

CView::checkin();

$affectation = new CAffectation();
$affectation->load($affectation_id);

if (!$depart) {
    $affectation_perm = new CAffectation();
    $affectation_perm->load($affectation_perm_id);

    // Suppression du blocage si présent
    $blocage = new CAffectation();

    $where = [
        "lit_id"    => "= '$affectation->lit_id'",
        "entree"    => "= '$affectation_perm->entree'",
        "sortie"    => "= '$affectation_perm->sortie'",
        "sejour_id" => "IS NULL",
    ];

    if ($blocage->loadObject($where)) {
        $blocage->delete();
    }

    // Départ du patient dans le lit choisi
    $new_aff = new CAffectation();
    $new_aff->cloneFrom($affectation);
    $new_aff->lit_id     = $lit_id;
    $new_aff->service_id = $service_id;
    $new_aff->entree     = "current";
    $new_aff->sortie     = $affectation_perm->sortie;
    $new_aff->effectue   = "0";


    $affectation_perm->effectue = "1";
    $affectation_perm->sortie   = "current";

    $msg = $affectation_perm->store();

    CAppUI::setMsg($msg ?: "CAffectation-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);
} else {
    $sejour = $affectation->loadRefSejour();

    $new_aff             = new CAffectation();
    $new_aff->cloneFrom($affectation);
    $new_aff->entree     = "current";
    $new_aff->sortie     = $affectation->sortie;
    $new_aff->lit_id     = $lit_id;
    $new_aff->service_id = $service_id;
    $new_aff->sejour_id  = $sejour->_id;
    $new_aff->parent_affectation_id = $affectation->parent_affectation_id;

    CSejour::$_cutting_affectation = true;

    $affectation->sortie = "current";
    $msg = $affectation->store();

    CAppUI::setMsg($msg ?: "CAffectation-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);
}

$msg = $new_aff->store();

CAppUI::setMsg($msg ?: "CAffectation-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);

echo CAppUI::getMsg();

if ($callback) {
    CAppUI::callbackAjax($callback);
}
