<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admissions;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CStoredObject;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

/**
 * Class CSejourLoader
 * @package Ox\Mediboard\Admissions
 */
abstract class CSejourLoader
{

    /**
     * @param array $sejours
     * @param array $praticiens
     * @param       $date
     * @param       $only_non_checked
     * @param       $alert_handler
     *
     * @throws \Exception
     */
    public static function loadSejoursForSejoursView(
        array $sejours,
        array $praticiens,
        string $date,
        bool $only_non_checked
    ): array {
        $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
        CPatient::massLoadIPP($patients);
        CStoredObject::massLoadBackRefs($patients, "dossier_medical");
        CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

        CStoredObject::massLoadFwdRef($sejours, "praticien_id");
        CStoredObject::massCountBackRefs($sejours, "tasks", ["realise" => "= '0'"], [], "taches_non_realisees");
        CStoredObject::massLoadBackRefs($sejours, "dossier_medical");
        CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC", ["annulee" => "= '0'"]);
        CSejour::massLoadSurrAffectation($sejours, "$date " . CmbDT::time());
        CSejour::massLoadBackRefs($sejours, "user_sejour");
        CSejour::massLoadNDA($sejours);

        $type_view_demande_particuliere = CAppUI::pref("type_view_demande_particuliere");
        $degre                          = $type_view_demande_particuliere == "last_macro" ? null : "low";
        if (in_array($type_view_demande_particuliere, ["trans_hight", "macro_hight"])) {
            $degre = "high";
        }
        $cible_importante = in_array(
            $type_view_demande_particuliere,
            ["last_macro", "macro_low", "macro_hight"]
        ) ? true : false;
        $important        = $cible_importante ? false : true;
        $dossiers         = [];

        $see_demande_ecap = CModule::getActive("brancardage") && CAppUI::gconf("brancardage General see_demande_ecap");
        $see_risque_pop   = CModule::getActive("dPprescription")
            && CAppUI::gconf("mpm Analyse_cat see_risque_pop");
        $alert_handler    = HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler');

        foreach ($sejours as $sejour) {
            $sejour->loadRefPatient()->updateBMRBHReStatus($sejour);
            $praticien = $sejour->loadRefPraticien();
            $sejour->checkDaysRelative($date);
            if ($see_demande_ecap) {
                $sejour->loadCurrBrancardage();
            }
            $sejour->countAlertsNotHandled("medium", "observation");
            $sejour->loadRefsOperations();
            $sejour->loadJourOp(CMbDT::date());
            $sejour->loadRefPrescriptionSejour();
            /** @var CPrescription $prescription */
            $prescription = $sejour->_ref_prescription_sejour;
            if ($prescription->_id) {
                $prescription->loadJourOp(CMbDT::date());
            }

            // Chargement des taches non effectuées
            $sejour->_count_tasks = $sejour->_count["taches_non_realisees"];


            $sejour->_count_tasks_not_created = 0;
            $sejour->_ref_tasks_not_created   = [];

            if ($prescription->_id) {
                // Chargement des lignes non associées à des taches
                $where                                         = [];
                $ljoin                                         = [];
                $ljoin["element_prescription"]                 =
                    "prescription_line_element.element_prescription_id = element_prescription.element_prescription_id";
                $ljoin["sejour_task"]                          =
                    "sejour_task.prescription_line_element_id = prescription_line_element.prescription_line_element_id";
                $where["prescription_id"]                      = " = '$prescription->_id'";
                $where["element_prescription.rdv"]             = " = '1'";
                $where["prescription_line_element.date_arret"] = " IS NULL";
                $where["active"]                               = " = '1'";
                $where[]                                       = "sejour_task.sejour_task_id IS NULL";
                $where["child_id"]                             = " IS NULL";

                $line_element                     = new CPrescriptionLineElement();
                $sejour->_count_tasks_not_created = $line_element->countList($where, null, $ljoin);

                if ($only_non_checked) {
                    $praticiens_ids = array_column($praticiens, "_id");
                    $prescription->countNoValideLines($praticiens_ids);
                    if ($prescription->_counts_no_valide == 0) {
                        unset($sejours[$sejour->_id]);
                        continue;
                    }
                }

                CPrescription::massAlertConfirmation($prescription);

                if (!$alert_handler) {
                    $prescription->countFastRecentModif();
                }
            }

            // Chargement des transmissions sur des cibles importantes
            $sejour->loadRefsTransmissions($cible_importante, $important, false, 1, null, $degre);
            $sejour->loadRefDossierMedical();

            /** @var CPatient $patient */
            $patient = $sejour->_ref_patient;
            $patient->loadRefPhotoIdentite();
            $patient->loadRefDossierMedical(false);

            if ($see_risque_pop && $patient->sexe == "f") {
                $patient->loadLastGrossesse();
            }

            $dossier_medical = $patient->_ref_dossier_medical;
            if ($dossier_medical->_id) {
                $dossiers[$dossier_medical->_id] = $dossier_medical;
            }
            $sejour->loadRefCurrAffectation("$date " . CMbDT::time());
            $sejour->_ref_curr_affectation->loadRefLit()->loadLastCleanup();
            $sejour->_ref_curr_affectation->_ref_lit->loadCompleteView();
        }

        $dossiers_id = CMbArray::pluck($sejours, "_ref_patient", "_ref_dossier_medical", "_id");

        // Suppressions des dossiers médicaux inexistants
        CMbArray::removeValue("", $dossiers);

        $_counts_allergie   = CDossierMedical::massCountAllergies($dossiers_id);
        $_counts_antecedent = CDossierMedical::massCountAntecedents($dossiers_id);

        /* @var CDossierMedical[] $dossiers */
        foreach ($dossiers as $_dossier) {
            $_dossier->loadRefsAllergies();
            $_dossier->_count_allergies   = array_key_exists(
                $_dossier->_id,
                $_counts_allergie
            ) ? $_counts_allergie[$_dossier->_id] : 0;
            $_dossier->_count_antecedents = array_key_exists(
                $_dossier->_id,
                $_counts_antecedent
            ) ? $_counts_antecedent[$_dossier->_id] : 0;
        }

        if ($alert_handler) {
            CPrescription::massCountAlertsNotHandled(CMbArray::pluck($sejours, "_ref_prescription_sejour"));
            CPrescription::massCountAlertsNotHandled(CMbArray::pluck($sejours, "_ref_prescription_sejour"), "high");
        }

        // Chargement des visites pour les séjours courants
        //    $visites = CSejour::countVisitesUser($sejours, $date, $praticien);

        $lits = CMbArray::pluck($sejours, "_ref_curr_affectation", "_ref_lit");

        $sorter_chambre       = CMbArray::pluck($lits, "_ref_chambre", "_view");
        $sorter_service       = CMbArray::pluck($lits, "_ref_chambre", "_ref_service", "_view");
        $sorter_lit           = CMbArray::pluck($lits, "_view");
        $sorter_sejour_sortie = CMbArray::pluck($sejours, "sortie");
        $sorter_sejour_entree = CMbArray::pluck($sejours, "entree");

        array_multisort(
            $sorter_service,
            SORT_ASC,
            $sorter_chambre,
            SORT_ASC,
            $sorter_lit,
            SORT_ASC,
            $sorter_sejour_sortie,
            SORT_ASC,
            $sorter_sejour_entree,
            SORT_DESC,
            $sejours
        );

        return $sejours;
    }
}
