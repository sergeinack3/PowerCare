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
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;

$entree     = CView::post("entree", "dateTime");
$sortie     = CView::post("sortie", "dateTime");
$callback   = CView::post("callback", "str");
$_mod_mater = CView::post("_mod_mater", "bool default|0");
$_date_split = CView::post('_date_split', 'str');

CView::checkin();

if ($_date_split === 'now') {
    $_date_split = CMbDT::dateTime();
}

$tolerance          = CAppUI::gconf("dPhospi CAffectation create_affectation_tolerance");
$modify_affectation = CMbDT::addDateTime("00:$tolerance:00", $entree) > $_date_split;

// Modifier la première affectation, affectation du lit si la tolérance de création d'affectation n'est pas atteint
$do = new CDoObjectAddEdit("CAffectation");

if ($modify_affectation) {
    $_POST["lit_id"] = $_POST["_new_lit_id"];
} else {
    $_POST["entree"] = $entree;
    $_POST["sortie"] = $_date_split;
}

$do->redirect      = null;
$do->redirectStore = null;
$do->doIt();

/** @var CAffectation $first_affectation */
$first_affectation = $do->_obj;

// Créer la seconde si la tolérance est dépassé
if (!$modify_affectation) {
    $do = new CDoObjectAddEdit("CAffectation", "affectation_id");

    $_POST["ajax"]           = 1;
    $_POST["entree"]         = $_date_split;
    $_POST["sortie"]         = $sortie;
    $_POST["lit_id"]         = $_POST["_new_lit_id"];
    $_POST["affectation_id"] = null;

    if ($_mod_mater) {
        $_POST["effectue"] = 0;
    }

    $do->doSingle(false);
}

// Gérer le déplacement du ou des bébés si nécessaire
if (CModule::getActive("maternite")) {
    /** @var CAffectation[] $affectations_enfant */
    $affectations_enfant = $first_affectation->loadBackRefs("affectations_enfant");

    if ($affectations_enfant) {
        foreach ($affectations_enfant as $_affectation) {
            $save_sortie = $_affectation->sortie;

            // Si le bébé a été détaché de la maman, recherche d'une affectation future
            $affectation_search = new CAffectation();

            $where = [
                'entree' => ">= '$save_sortie'",
                'sejour_id' => "= '$_affectation->sejour_id'"
            ];

            if ($affectation_search->loadObject($where)) {
                $_affectation = $affectation_search;
                $save_sortie = $_affectation->sortie;
            }

            $modify_affectation_enfant = CMbDT::addDateTime(
                    "00:$tolerance:00",
                    $_affectation->entree
                ) > $_date_split;

            if ($modify_affectation_enfant) {
                $_affectation->lit_id = $_POST["_new_lit_id"];
            } else {
                $_affectation->sortie = $_date_split;
            }

            if ($msg = $_affectation->store()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
            }

            if (!$modify_affectation_enfant) {
                $affectation                        = new CAffectation;
                $affectation->lit_id                = $_POST["_new_lit_id"];
                $affectation->sejour_id             = $_affectation->sejour_id;
                $affectation->parent_affectation_id = $do->_obj->_id;
                $affectation->entree                = $_date_split;
                $affectation->sortie                = $save_sortie;

                if ($msg = $affectation->store()) {
                    CAppUI::setMsg($msg, UI_MSG_ERROR);
                }
            }
        }
    } else {
        $naissance = $first_affectation->_ref_sejour->loadRefNaissance();

        if ($naissance && $naissance->_id) {
            $sejour_maman       = $naissance->loadRefSejourMaman();
            $maman              = $sejour_maman->loadRefPatient();
            $affectations_maman = $sejour_maman->loadRefsAffectations();

            CAppUI::callbackAjax(
                "Placement.associatedAffectation",
                $do->_obj->_id,
                $_POST["_date_split"],
                $_POST["_new_lit_id"],
                $tolerance
            );
        }
    }
}

// La possible réinstanciation du $do fait perdre le callback
$do->callBack = $callback;

$do->doRedirect();
