<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$affectation_id = CView::post("affectation_id", "ref class|CAffectation");
$_date_cut      = CView::post("_date_cut", "dateTime");
$lit_id         = CView::post("lit_id", "ref class|CLit");
$_action_maman  = CView::post("_action_maman", "str");
$callback       = CView::post("callback", "str");
$callback_etab  = CView::post("callback_etab", "bool default|0");
$service_id     = CView::post("service_id", "ref class|CService");

$uf_hebergement_id = CView::post("uf_hebergement_id", "ref class|CUniteFonctionnelle");
$uf_medicale_id    = CView::post("uf_medicale_id", "ref class|CUniteFonctionnelle");
$uf_soins_id       = CView::post("uf_soins_id", "ref class|CUniteFonctionnelle");

$affectation = new CAffectation();
$affectation->load($affectation_id);

if ($_date_cut < $affectation->entree || $_date_cut > $affectation->sortie) {
    CAppUI::setMsg("Date de scission hors des bornes de l'affectation", UI_MSG_ERROR);
    echo CAppUI::getMsg();
    CApp::rip();
}

$tolerance = CAppUI::gconf("dPhospi CAffectation create_affectation_tolerance");

if (CMbDT::addDateTime("00:$tolerance:00", $affectation->entree) > $_date_cut) {
    $affectation_cut = $affectation;
} else {
    $affectation_cut                        = new CAffectation();
    $affectation_cut->entree                = $_date_cut;
    $affectation_cut->sejour_id             = $affectation->sejour_id;
    $affectation_cut->sortie                = $affectation->sortie;
    $affectation_cut->parent_affectation_id = $affectation->parent_affectation_id;
    $affectation_cut->effectue              = 0;
    $affectation->sortie = $_date_cut;
}

if ($service_id) {
    $affectation_cut->service_id = $service_id;
} else {
    $affectation_cut->lit_id = $affectation->lit_id;
}

$affectation_cut->uf_hebergement_id = $uf_hebergement_id;
$affectation_cut->uf_medicale_id    = $uf_medicale_id;
$affectation_cut->uf_soins_id       = $uf_soins_id;

if ($lit_id) {
    $affectation_cut->lit_id = $lit_id;
}

$save_parent_affectation_id = $affectation_cut->parent_affectation_id;

// Détachement de la maman si la checkbox est cochée
if ($save_parent_affectation_id && $_action_maman) {
    $affectation_cut->parent_affectation_id = null;
}

// Rattachement à l'affectation de la maman si la checkbox est cochée
if ($_action_maman && !$save_parent_affectation_id) {
    $naissance                   = new CNaissance();
    $naissance->sejour_enfant_id = $affectation->sejour_id;
    $naissance->loadMatchingObject();

    if ($naissance->_id) {
        $sejour_maman      = $naissance->loadRefSejourMaman();
        $affectation_maman = $sejour_maman->getCurrAffectation($_date_cut);
        if ($affectation_maman->_id) {
            $affectation_cut->lit_id                = $affectation_maman->lit_id;
            $affectation_cut->parent_affectation_id = $affectation_maman->_id;
        }
    }
}

CSejour::$_cutting_affectation = true;

if ($msg = $affectation->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
}

if ($msg = $affectation_cut->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
}

// Scinder également les affectations des enfants
if ($affectation->countBackRefs("affectations_enfant")) {
    $affectations_enfant = $affectation->loadBackRefs("affectations_enfant");

    foreach ($affectations_enfant as $_affectation_enfant) {
        /** @var CAffectation $_affectation_enfant */
        if (CMbDT::addDateTime("00:$tolerance:00", $_affectation_enfant->entree) > $_date_cut) {
            $_affectation = $_affectation_enfant;
        } else {
            $_affectation                    = new CAffectation();
            $_affectation->entree            = $_date_cut;
            $_affectation->sejour_id         = $_affectation_enfant->sejour_id;
            $_affectation->sortie            = $_affectation_enfant->sortie;
            $_affectation->uf_hebergement_id = $_affectation_enfant->uf_hebergement_id;
            $_affectation->uf_medicale_id    = $_affectation_enfant->uf_medicale_id;
            $_affectation->uf_soins_id       = $_affectation_enfant->uf_soins_id;

            $_affectation_enfant->sortie = $_date_cut;
        }

        $_affectation->lit_id                = $lit_id ? $lit_id : $_affectation_enfant->lit_id;
        $_affectation->parent_affectation_id = $affectation_cut->_id;

        if ($msg = $_affectation_enfant->store()) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);
        }

        if ($msg = $_affectation->store()) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);
        }
    }
}

if ($callback) {
    $fields = $affectation_cut->getProperties();

    $ask_etab_externe = CAppUI::gconf("dPhospi placement ask_etab_externe");

    $fields["_ask_etab_externe"] = $ask_etab_externe
        && $affectation_cut->loadRefSejour()->patient_id && $affectation_cut->loadRefService()->externe;
    CAppUI::callbackAjax($callback, $affectation_cut->_id, $fields);
} elseif ($callback_etab && $affectation_cut->_ref_service->externe) {
    CAppUI::callbackAjax(
        "(function(affectationId) { setTimeout( function() {openModalEtab(affectationId)}, 1000); })",
        $affectation_cut->_id,
        $fields
    );
}

echo CAppUI::getMsg();
CApp::rip();
