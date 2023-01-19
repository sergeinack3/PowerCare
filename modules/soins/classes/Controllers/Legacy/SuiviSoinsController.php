<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Controllers\Legacy;

use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Mpm\CPrescriptionLineMix;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\PrescriptionService;

/**
 * Class SuiviSoinsController
 * @package Ox\Mediboard\Soins\Controllers\Legacy
 */
class SuiviSoinsController extends CLegacyController
{
    /**
     *  Display the view of the care follow-up folder
     */
    public function ajaxViewDossierSuivi(): void
    {
        $this->checkPermRead();

        $sejour_id = CView::get("sejour_id", "ref class|CSejour");
        $date      = CView::get("date", "date");
        CView::checkin();

        $group = CGroups::loadCurrent();

        $sejour = new CSejour();
        $sejour->load($sejour_id);

        CAccessMedicalData::logAccess($sejour);

        $sejour->countTasks();
        $sejour->countRDVExternes();
        $sejour->countObjectifsSoins();
        $sejour->countObjectifsDelayWeek();
        $sejour->countAlertsNotHandled("medium", "observation");
        $sejour->isInPermission();

        $prescription_active = CModule::getActive("dPprescription");
        $plan_soins_active   = $prescription_active ? CPrescription::isPlanSoinsActive() : false;
        $prescription        = $sejour->loadRefPrescriptionSejour();

        $count_perop_adm = 0;
        if ($prescription_active && $plan_soins_active) {
            if (CAppUI::conf("dPprescription general show_perop_suivi_soins", $group->_guid) && $prescription->_id) {
                $count_perop_adm = CAdministration::countPerop($prescription->_id);
            }

            if (CAppUI::gconf("dPprescription general show_perop_suivi_soins")) {
                $last_operation = $sejour->loadRefLastOperation(true);
                $last_operation->loadRefsAnesthPerops();

                if ($last_operation->_id && $last_operation->_ref_anesth_perops) {
                    $count_perop_adm += $last_operation->_count_anesth_perops;
                }
            }
        }

        $this->renderSmarty(
            "inc_dossier_suivi",
            [
                "sejour"            => $sejour,
                "date"              => $date,
                "count_perop_adm"   => $count_perop_adm,
                "plan_soins_active" => $plan_soins_active,
                "prescription"      => $prescription,
            ]
        );
    }

    public function ajax_vw_commentaire_pharma()
    {
        $this->checkPermRead();

        $praticien_id = CView::get("prat_id", "ref class|CMediusers");
        $date         = CView::get("date_com_pharma", "date default|" . CMbDT::date());
        $function_id  = CView::get("function_id", "ref class|CFunctions", true);

        CView::checkin();

        $prescription_service = new PrescriptionService();
        $lines_com_pharma     = $prescription_service->getLinesWithPharmaComments($date, $praticien_id, $function_id);

        $prescriptions = [];
        foreach ($lines_com_pharma as $_line) {
            /* @var CPrescriptionLineMedicament|CPrescriptionLineMix $_line */
            $prescriptions[$_line->_ref_prescription->_guid] = $_line->_ref_prescription;
        }

        foreach ($prescriptions as $_prescription) {
            /* @var CPrescription $_prescription */
            $_prescription->loadRefPatient();
            $_prescription->loadJourOp(CMbDT::date());

            $patient = $_prescription->_ref_patient;
            $sejour  = $_prescription->_ref_object;

            $patient->loadIPP();
            $patient->loadRefPhotoIdentite();
            $sejour->loadRefPraticien();
            $sejour->checkDaysRelative(CMbDT::date());
            $sejour->loadSurrAffectations('$date 00:00:00');
            $sejour->loadNDA();
            $sejour->loadRefCurrAffectation()->loadRefLit()->loadLastCleanup();
            $sejour->_ref_curr_affectation->updateView();
            $patient->loadRefDossierMedical();
            $dossier_medical = $patient->_ref_dossier_medical;

            if ($dossier_medical->_id) {
                $dossier_medical->loadRefsAllergies();
                $dossier_medical->loadRefsAntecedents();
                $dossier_medical->countAntecedents();
                $dossier_medical->countAllergies();
            }
        }

        $this->renderSmarty(
            "vw_reeval_antibio",
            [
                "prescriptions" => $prescriptions,
                "date"          => $date,
                "type"          => "com_pharma",
            ]
        );
    }

    public function ajax_vw_reeval_antibio()
    {
        $this->checkPermRead();

        $praticien_id = CView::get("prat_id", "ref class|CMediusers");
        $date_reeval  = CView::get("date_reeval", "date default|" . CMbDT::date());
        $function_id  = CView::get("function_id", "ref class|CFunctions", true);

        CView::checkin();

        $prescription_service = new PrescriptionService();
        $prescriptions        = $prescription_service->getPrescriptionWithLinesAntibio(
            $date_reeval,
            $praticien_id,
            $function_id
        );

        foreach ($prescriptions as $_prescription) {
            /* @var CPrescription $_prescription */
            $_prescription->loadRefPatient();
            $_prescription->loadJourOp(CMbDT::date());

            $patient = $_prescription->_ref_patient;
            $sejour  = $_prescription->_ref_object;

            $patient->loadIPP();
            $patient->loadRefPhotoIdentite();
            $sejour->loadRefPraticien();
            $sejour->checkDaysRelative(CMbDT::date());
            $sejour->loadSurrAffectations('$date_reeval 00:00:00');
            $sejour->loadNDA();
            $patient->loadRefDossierMedical();
            $sejour->loadRefCurrAffectation()->loadRefLit()->loadLastCleanup();
            $sejour->_ref_curr_affectation->updateView();
            $dossier_medical = $patient->_ref_dossier_medical;

            if ($dossier_medical->_id) {
                $dossier_medical->loadRefsAllergies();
                $dossier_medical->loadRefsAntecedents();
                $dossier_medical->countAntecedents();
                $dossier_medical->countAllergies();
            }
        }

        $this->renderSmarty(
            "vw_reeval_antibio",
            [
                "prescriptions" => $prescriptions,
                "date"          => $date_reeval,
                "type"          => "reeval_antibio",
            ]
        );
    }

    public function httpreq_vw_bilan_list_prescriptions()
    {
        $this->checkPermRead();

        $user              = CMediusers::get();
        $praticien_id      = CView::get("prat_bilan_id", "ref class|CMediusers default|$user->_id", true);
        $date_min          = CView::get("_date_entree_prevue", "date default|now", true);  // par default, date du jour
        $date_max          = CView::get("_date_sortie_prevue", "date default|now", true);
        $board             = CView::get("board", "bool default|0");
        $signee            = CView::get("signee", "enum list|0|all default|0", true); // par default les non signees
        $type_prescription = CView::get(
            "type_prescription",
            "enum list|sejour|sortie_manquante default|sejour",
            true
        ); // sejour - sortie_manquante
        $function_id       = CView::get("function_id", "ref class|CFunctions", true);

        CView::enableSlave();
        CView::checkin();

        $date_min = $date_min . " 00:00:00";
        $date_max = $date_max . " 23:59:59";

        $prescription_service = new PrescriptionService();
        $prescriptions        = $prescription_service->getPrescriptions(
            $date_min,
            $date_max,
            $type_prescription,
            $signee,
            $praticien_id,
            $function_id
        );

        $sejours = [];

        foreach ($prescriptions as $_prescription) {
            $sejours[$_prescription->object_id] = $_prescription->_ref_object;
        }

        $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
        CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
        CSejour::massLoadNDA($sejours);
        CPatient::massLoadIPP($patients);
        CPatient::massCountPhotoIdentite($patients);

        /** @var CSejour $_sejour */
        foreach ($sejours as $_sejour) {
            $_sejour->loadRefPatient()->updateBMRBHReStatus($_sejour);
        }

        if (count($prescriptions)) {
            CMbArray::pluckSort($prescriptions, SORT_ASC, "_ref_object", "_ref_patient", "nom");
        }

        $sejour            = new CSejour();
        $sejour->_date_min = $date_min;
        $sejour->_date_max = $date_max;

        $now = CMbDT::date();

        // Reorder by practionner type
        $prescriptions_by_praticiens_type = [];

        foreach ($prescriptions as $_prescription) {
            $_prescription->loadRefPatient();

            $patient = $_prescription->_ref_patient;
            /** @var CSejour $sejour */
            $sejour  = $_prescription->_ref_object;

            $patient->loadRefPhotoIdentite();

            $praticien = $sejour->loadRefPraticien();
            $sejour->checkDaysRelative($now);
            $sejour->loadSurrAffectations($date_min);

            $sejour->loadRefCurrAffectation()->loadRefLit()->loadLastCleanup();
            $sejour->_ref_curr_affectation->updateView();

            if ($_prescription->_id) {
                $_prescription->loadJourOp($now);
            }

            $patient->loadRefDossierMedical();
            $dossier_medical = $patient->_ref_dossier_medical;

            if ($dossier_medical->_id) {
                $dossier_medical->loadRefsAllergies();
                $dossier_medical->loadRefsAntecedents();
                $dossier_medical->countAntecedents();
                $dossier_medical->countAllergies();
            }

            $prescriptions_by_praticiens_type[$praticien->_user_type_view][$_prescription->_id] = $_prescription;
        }

        $counter_prescription = $prescriptions && count($prescriptions) ? count($prescriptions) : 0;

        $this->renderSmarty(
            "inc_vw_bilan_list_prescriptions",
            [
                "prescriptions_by_praticiens_type" => $prescriptions_by_praticiens_type,
                "board"                            => $board,
                "date"                             => $date_min,
                "default_tab"                      => "prescription_sejour",
                "counter_prescription"             => $counter_prescription,
            ]
        );
    }

    public function httpreq_vw_bilan_list_inscriptions()
    {
        $this->checkPermRead();

        $user         = CMediusers::get();
        $praticien_id = CView::get("prat_bilan_id", "ref class|CMediusers default|$user->_id", true);
        $date_min     = CView::get("_date_entree_prevue", "date default|now", true);  // par default, date du jour
        $date_max     = CView::get("_date_sortie_prevue", "date default|now", true);
        $function_id  = CView::get("function_id", "ref class|CFunctions", true);
        $board        = CView::get("board", "bool default|0");

        CView::checkin();
        CView::enableSlave();

        $date_min = $date_min . " 00:00:00";
        $date_max = $date_max . " 23:59:59";

        $prescription_service = new PrescriptionService();
        $prescriptions = $prescription_service->getPrescriptionWithInscription($date_min, $date_max, $praticien_id, $function_id);

        $sejours = CStoredObject::massLoadFwdRef($prescriptions, "object_id", "CSejour");
        CStoredObject::massLoadFwdRef($sejours, "praticien_id");
        CSejour::massLoadNDA($sejours);
        $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
        CStoredObject::massLoadBackRefs($patients, "dossier_medical");
        CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
        CPatient::massLoadIPP($patients);
        CPatient::massCountPhotoIdentite($patients);
        foreach ($prescriptions as $_presc) {
            $_presc->loadRefObject();
            $_presc->loadRefPatient();
        }

        if (count($prescriptions)) {
            CMbArray::pluckSort($prescriptions, SORT_ASC, "_ref_object", "_ref_patient", "nom");
        }

        $sejour            = new CSejour();
        $sejour->_date_min = $date_min;
        $sejour->_date_max = $date_max;

        // Reorder by practionner type
        $prescriptions_by_praticiens_type = [];

        /* @var CPrescription $_prescription */
        foreach ($prescriptions as $_prescription) {
            $patient = $_prescription->_ref_patient;
            $sejour  = $_prescription->_ref_object;

            $patient->loadRefPhotoIdentite();
            $patient->updateBMRBHReStatus($sejour);

            $praticien = $sejour->loadRefPraticien();
            $sejour->checkDaysRelative(CMbDT::date());
            $sejour->loadSurrAffectations($date_min);
            $sejour->loadRefCurrAffectation()->loadRefLit()->loadLastCleanup();
            $sejour->_ref_curr_affectation->updateView();

            if ($_prescription->_id) {
                $_prescription->loadJourOp(CMbDT::date());
            }

            $patient->loadRefDossierMedical();
            $dossier_medical = $patient->_ref_dossier_medical;

            if ($dossier_medical->_id) {
                $dossier_medical->loadRefsAllergies();
                $dossier_medical->loadRefsAntecedents();
                $dossier_medical->countAntecedents();
                $dossier_medical->countAllergies();
            }

            $prescriptions_by_praticiens_type[$praticien->_user_type_view][$_prescription->_id] = $_prescription;
        }

        $counter_prescription = $prescriptions && count($prescriptions) ? count($prescriptions) : 0;

        // Smarty template
        $this->renderSmarty(
            "inc_vw_bilan_list_prescriptions",
            [
                "prescriptions_by_praticiens_type" => $prescriptions_by_praticiens_type,
                "board"                            => $board,
                "date"                             => $date_min,
                "default_tab"                      => "prescription_sejour",
                "default_id"                       => "inscriptions",
                "counter_prescription"             => $counter_prescription,
            ]
        );
    }
}
