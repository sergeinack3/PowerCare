<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

// Pour les patients avec un dossier volumineux
CApp::setTimeLimit(36000);
CApp::setMemoryLimit("4096M");

CCanDo::checkRead();

$patient_id   = CView::get("patient_id", "ref class|CPatient", true);
$vw_cancelled = CView::get("vw_cancelled", "bool default|0");

// Récuperation du patient sélectionné
$patient = new CPatient();
if (CView::get("new", "bool default|0")) {
    $patient->load(null);
    CView::setSession("id", null);
} else {
    $patient->load($patient_id);
}

CView::checkin();

$nb_sejours_annules   = 0;
$nb_ops_annulees      = 0;
$nb_consults_annulees = 0;

if ($patient->_id) {
    $patient->needsRead();
    $patient->loadDossierComplet(null, false, 100);
    $patient->loadIPP();
    $patient->loadPatientLinks();
    $patient->countINS();
    $patient->updateBMRBHReStatus();
    $patient->loadRefSourceIdentite();
    $patient->loadRefPatientState();
    $patient->updateNomPaysInsee();
    $patient->loadCodeInseeNaissance();
    $patient->loadRefPatientINSNIR();
    $patient->loadRefMedecinTraitantExercicePlace();
    $patient->loadRefsCorrespondants();

    CStoredObject::massLoadFwdRef($patient->_ref_medecins_correspondants, 'medecin_exercice_place_id');
    foreach ($patient->_ref_medecins_correspondants as $_corres) {
        $_corres->loadRefMedecinExercicePlace();
    }

    if (CModule::getActive("fse")) {
        $cv = CFseFactory::createCV();
        if ($cv) {
            $cv->loadIdVitale($patient);
        }
    }

    if (!$vw_cancelled) {
        foreach ($patient->_ref_sejours as $_key => $_sejour) {
            $_sejour->loadRefRPU();
            foreach ($_sejour->_ref_operations as $_key_op => $_operation) {
                if ($_operation->annulee) {
                    unset($_sejour->_ref_operations[$_key_op]);
                    $nb_ops_annulees++;
                }
            }
            if ($_sejour->annule) {
                unset($patient->_ref_sejours[$_key]);
                $nb_sejours_annules++;
            }
        }
        // Suppression des consultations annulees
        foreach ($patient->_ref_consultations as $consult) {
            if ($consult->annule) {
                unset($patient->_ref_consultations[$consult->_id]);
                $nb_consults_annulees++;
            }
        }
    }
}

// Iconographie du patient sur les systèmes tiers
$group_id = CGroups::loadCurrent()->_id;
$patient->loadExternalIdentifiers($group_id);

$events_by_date = $patient->getTimeline();

$sejours_by_NDA = [];

// Grouper les séjours par NDA
foreach ($events_by_date as $annee => $_events_by_year) {
    foreach ($_events_by_year as $date => $_events_by_date) {
        foreach ($_events_by_date as $_event) {
            $object = $_event["event"];
            if ($object instanceof CSejour) {
                $sejours_by_NDA[$object->_NDA][] = $object;
            }
        }
    }
}

$manager = new CRGPDManager($group_id);

if ($manager->isEnabledFor($patient)) {
    $consent = $manager->getConsentForObject($patient);
    $patient->setRGPDConsent($consent);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);
$smarty->assign("canPatients", CModule::getCanDo("dPpatients"));
$smarty->assign("canAdmissions", CModule::getCanDo("dPadmissions"));
$smarty->assign("canPlanningOp", CModule::getCanDo("dPplanningOp"));
$smarty->assign("canCabinet", CModule::getCanDo("dPcabinet"));
$smarty->assign("nb_sejours_annules", $nb_sejours_annules);
$smarty->assign("nb_ops_annulees", $nb_ops_annulees);
$smarty->assign("nb_consults_annulees", $nb_consults_annulees);
$smarty->assign("vw_cancelled", $vw_cancelled);
$smarty->assign("events_by_date", $events_by_date);
$smarty->assign("sejours_by_NDA", $sejours_by_NDA);
$smarty->assign('rgpd_manager', $manager);

$smarty->display("inc_vw_patient");
