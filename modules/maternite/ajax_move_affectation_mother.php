<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $m;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

$affectation_id = CView::get("affectation_id", "ref class|CAffectation");
$date_split     = CView::get("date_split", "dateTime");
$new_lit_id     = CView::get("new_lit_id", "ref class|CLit");
CView::checkin();

$tolerance         = CAppUI::gconf("dPhospi CAffectation create_affectation_tolerance");
$first_affectation = CAffectation::find($affectation_id);
$sejour            = $first_affectation->loadRefSejour();
$naissance         = $sejour->loadRefNaissance();

$save_lit_id = null;

if ($naissance && $naissance->_id) {
    $sejour_maman       = $naissance->loadRefSejourMaman();
    $maman              = $sejour_maman->loadRefPatient();
    $affectations_maman = $sejour_maman->loadRefsAffectations();
    $affectation_maman  = $sejour_maman->_ref_last_affectation;

    if (
        $affectation_maman->_id
        && ($date_split >= $affectation_maman->entree)
        && ($date_split <= $affectation_maman->sortie)
    ) {
        CSejour::$delete_aff_hors_sejours = false;

        $save_sortie = $sejour_maman->sortie;
        $save_lit_id = $affectation_maman->lit_id;

        $modify_affectation_maman = CMbDT::addDateTime("00:$tolerance:00", $affectation_maman->entree) > CMbDT::dateTime();

        if ($modify_affectation_maman) {
            $affectation_maman->lit_id = $new_lit_id;
        } else {
            $affectation_maman->sortie = $date_split;
        }

        $msg = $affectation_maman->store();
        CAppUI::setMsg($msg ?: 'CAffectation-msg-modify', $msg ? UI_MSG_ERROR : UI_MSG_OK);

        if (!$modify_affectation_maman) {
            $affectation                        = new CAffectation;
            $affectation->lit_id                = $new_lit_id;
            $affectation->sejour_id             = $affectation_maman->sejour_id;
            $affectation->entree                = $date_split;
            $affectation->sortie                = $save_sortie;

            $msg = $affectation->store();

            CAppUI::setMsg($msg ?: 'CAffectation-msg-create', $msg ? UI_MSG_ERROR : UI_MSG_OK);
        }

        $new_affectation_maman = isset($affectation) ? $affectation : $affectation_maman;

        $first_affectation->parent_affectation_id = $new_affectation_maman->_id;

        $msg = $first_affectation->store();

        CAppUI::setMsg($msg ?: 'CAffectation-msg-modify', $msg ? UI_MSG_ERROR : UI_MSG_OK);

        CAppUI::callbackAjax('Placement.refreshCurrPlacement');

        CSejour::$delete_aff_hors_sejours = true;
    }
}

echo CAppUI::getMsg();
CApp::rip();
