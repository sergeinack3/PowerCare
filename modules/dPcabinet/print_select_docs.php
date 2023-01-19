<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::check();

global $can;

$consultation_id = CValue::get("consultation_id");
$sejour_id       = CValue::get("sejour_id");

$count_prescription = 0;
if (CModule::getActive("dPprescription")) {
    // Chargement de la prescription de pre-admission
    $prescription_preadm = new CPrescription();
    $prescription_sejour = new CPrescription();
    $prescription_sortie = new CPrescription();

    if ($sejour_id) {
        $prescription_sortie->object_id    = $prescription_sejour->object_id = $prescription_preadm->object_id = $sejour_id;
        $prescription_sortie->object_class = $prescription_sejour->object_class = $prescription_preadm->object_class = "CSejour";

        $prescription_preadm->type = "pre_admission";
        $prescription_preadm->loadMatchingObject();
        if ($prescription_preadm->_id) {
            $count_prescription++;
        }
        $prescription_sejour->type = "sejour";
        $prescription_sejour->loadMatchingObject();
        if ($prescription_sejour->_id) {
            $count_prescription++;
        }
        $prescription_sortie->type = "sortie";
        $prescription_sortie->loadMatchingObject();
        if ($prescription_sortie->_id) {
            $count_prescription++;
        }
    }
}

// Consultation courante
$consult = CConsultation::findOrFail($consultation_id);
$can->edit &= $consult->canEdit();

$can->needsEdit();

$use_moebius = (bool)(CModule::getActive("moebius") && CAppUI::pref('ViewConsultMoebius'));

$documents = [
    "docs"  => [],
    "files" => [],
];

$nb_files = 0;

$nb_docs = $consult->loadRefsDocs();

$documents["docs"]  = $consult->_ref_documents;

if ($use_moebius) {
    $nb_files = $consult->loadRefsFiles();
    $documents["files"] = $consult->_ref_files;
}

$documents["counter"] = $nb_docs + $nb_files;

// Création du template
$smarty = new CSmartyDP();
if (CModule::getActive("dPprescription")) {
    $smarty->assign("prescription_preadm", $prescription_preadm);
    $smarty->assign("prescription_sejour", $prescription_sejour);
    $smarty->assign("prescription_sortie", $prescription_sortie);
}
$smarty->assign("count_prescription", $count_prescription);
$smarty->assign("consult", $consult);
$smarty->assign("use_moebius", $use_moebius);
$smarty->assign("documents", $documents);
$smarty->display("print_select_docs");

