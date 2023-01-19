<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class ConsultationOfflineLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function vw_offline_consult_patients(): void
    {
        $this->checkPermRead();

        CApp::setMemoryLimit("512M");

        $function_id = CView::get("function_id", "ref class|CFunctions");
        $chir_ids    = CView::get("chir_ids", "str");
        $date        = CView::get("date", "date default|" . CMbDT::date());

        CView::checkin();
        CView::enforceSlave();

        // Praticiens sélectionnés
        $user       = new CMediusers();
        $praticiens = [];
        if ($function_id) {
            $praticiens = CConsultation::loadPraticiens(PERM_EDIT, $function_id);
        }
        if ($chir_ids) {
            $praticiens = $user->loadAll(explode("-", $chir_ids));
        }

        //plages de consultation
        $where            = [];
        $where["chir_id"] = CSQLDataSource::prepareIn(array_keys($praticiens));
        $where["date"]    = "= '$date'";
        $plage            = new CPlageconsult();
        $plages           = $plage->loadList($where, ["debut"]);

        $nbConsultations = 0;
        $resumes_patient = [];

        $where_massload_consults = [
            'annule' => "= '0'",
            'chrono' => "!=  '" . CConsultation::TERMINE . "'",
        ];

        $where_massload_count_patients = [
            'annule'            => "= '0'",
            "type_consultation" => "= 'consultation'",
            'patient_id'        => 'IS NOT NULL',
        ];

        $order_acte_ccam = [
            'code_association ASC',
            'code_acte',
            'code_activite',
            'code_phase',
            'acte_id',
        ];

        [$where_consults, $ljoin_consults, $order_consults] = CPatient::getConstraintsForConsultations();

        $prats = CStoredObject::massLoadFwdRef($plages, "chir_id");
        CStoredObject::massLoadFwdRef($prats, "function_id");
        CPlageconsult::massLoadFillRate($plages);
        CStoredObject::massCountBackRefs(
            $plages,
            'consultations',
            $where_massload_count_patients,
            null,
            CPlageconsult::BACKREF_CONSULTS_PATIENTS
        );
        $consults = CStoredObject::massLoadBackRefs($plages, 'consultations', 'heure', $where_massload_consults);
        $patients = CStoredObject::massLoadFwdRef($consults, 'patient_id');
        CStoredObject::massLoadBackRefs($patients, "dossier_medical");
        CStoredObject::massCountBackRefs($patients, "files");
        CStoredObject::massCountBackRefs($patients, "documents");
        CStoredObject::massLoadBackRefs($patients, "correspondants");
        $consults_patient = CStoredObject::massLoadBackRefs(
            $patients,
            'consultations',
            $order_consults,
            $where_consults,
            $ljoin_consults
        );

        CStoredObject::massLoadBackRefs($consults_patient, 'actes_ccam', $order_acte_ccam);
        CStoredObject::massLoadBackRefs($consults_patient, 'actes_ngap', 'lettre_cle DESC, execution DESC');

        /** @var $plages CPlageConsult[] */
        foreach ($plages as $_plage_consult) {
            $_plage_consult->loadRefsConsultations(false, false);
            $_plage_consult->loadRefChir();
            $_plage_consult->countPatients();
            $_plage_consult->_ref_chir->loadRefFunction();

            foreach ($_plage_consult->_ref_consultations as $_consult) {
                if (!$_consult->patient_id || isset($resumes_patient[$_consult->patient_id])) {
                    continue;
                }

                $patient = $_consult->loadRefPatient();
                $patient->loadDossierComplet();
                $patient->loadRefDossierMedical();

                foreach ($patient->_ref_consultations as $__consult) {
                    $_latest_constantes            = CConstantesMedicales::getLatestFor(
                        $patient,
                        null,
                        ["poids", "taille"],
                        $__consult
                    );
                    $__consult->_latest_constantes = $_latest_constantes[0];

                    $__consult->loadRefsDocItems(false);
                    $__consult->countDocItems();
                    $__consult->loadRefsActesCCAM();
                    $__consult->loadRefsActesNGAP();
                    $__consult->loadRefFacture()->loadRefsReglements();
                    $__consult->loadRefPlageConsult();
                }

                $resumes_patient[$patient->_id] = $this->renderSmarty(
                    'vw_resume',
                    [
                        'offline' => 1,
                        'patient' => $_consult->_ref_patient,
                    ],
                    null,
                    true
                );

                if ($_consult->patient_id) {
                    $nbConsultations++;
                }
            }
        }

        $this->renderSmarty(
            'vw_offline/consult_patients',
            [
                'plages'          => $plages,
                'nbConsultations' => $nbConsultations,
                'praticiens'      => $praticiens,
                'resumes_patient' => $resumes_patient,
                'date'            => $date,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function offline_programme_consult(): void
    {
        $this->checkPermRead();

        CApp::setMemoryLimit("512M");

        $ds = CSQLDataSource::get("std");

        $function_id = CView::get("function_id", "ref class|CFunctions");
        $chir_ids    = CView::get("chir_ids", "str");
        $date        = CView::get("date", "date default|" . CMbDT::date());
        $period      = CView::get("period", "str default|12-weeks");

        CView::checkin();
        CView::enforceSlave();

        // Praticiens sélectionnés
        $user       = new CMediusers();
        $praticiens = [];
        if ($function_id) {
            $praticiens = CConsultation::loadPraticiens(PERM_EDIT, $function_id);
        }

        if ($chir_ids) {
            $praticiens = $user->loadAll(explode("-", $chir_ids));
        }

        // Bornes de dates
        [$period_count, $period_type] = explode("-", $period);
        $period_count++;
        $date_min = CMbDT::date($date);
        if (!$period) {
            $date_max = CMbDT::date("last day of week", $date);
        } else {
            $date_max = CMbDT::date("+ $period_count $period_type - 1 day", $date);
        }

        // Chargement de toutes les plages concernées
        $where = [
            "chir_id" => CSQLDataSource::prepareIn(array_keys($praticiens)),
            "date"    => $ds->prepare("BETWEEN %1 AND %2", $date_min, $date_max),
        ];
        $order = "date, debut";

        $plage = new CPlageconsult();
        /** @var CPlageconsult[] $plages */
        $plages = $plage->loadList($where, $order);

        /** @var CPlageconsult[][] $plages Plages par mois */
        $listPlages = [];

        $bank_holidays = array_merge(CMbDT::getHolidays($date_min), CMbDT::getHolidays($date_max));

        $totals = [];

        $consultations = CStoredObject::massLoadBackRefs(
            $plages,
            'consultations',
            'heure',
            ['type_consultation' => "= 'consultation'"]
        );
        CStoredObject::massLoadFwdRef($plages, 'agenda_praticien_id');
        CPlageconsult::massLoadFillRate($plages);

        CStoredObject::massLoadFwdRef($consultations, "patient_id");
        CStoredObject::massLoadFwdRef($consultations, "categorie_id");
        CStoredObject::massLoadFwdRef($consultations, "element_prescription_id");

        // Optimisation du chargement patient
        $patient                 = new CPatient();
        $patient->_spec->columns = ["nom", "prenom", "nom_jeune_fille", "civilite"];

        // Chargement des places disponibles pour chaque plage
        foreach ($plages as $_plage) {
            // Classement par mois
            $month                = CMbDT::format($_plage->date, "%B_%Y");
            $listPlages[$month][] = $_plage;

            // Praticien
            $_plage->_ref_chir = $praticiens[$_plage->chir_id];
            $_plage->_ref_chir->loadRefFunction();

            // Totaux
            if (!isset($totals[$month])) {
                $totals[$month] = [
                    "affected" => 0,
                    "total"    => 0,
                ];
            }

            $totals[$month]["affected"] += $_plage->_affected;
            $totals[$month]["total"]    += $_plage->_total;

            $_plage->loadRefAgendaPraticien();

            // Détails des consultations
            $_plage->_listPlace = [];
            for ($i = 0; $i < $_plage->_total; $i++) {
                $minutes                                 = $_plage->_freq * $i;
                $_plage->_listPlace[$i]["time"]          = CMbDT::time("+ $minutes minutes", $_plage->debut);
                $_plage->_listPlace[$i]["consultations"] = [];
            }

            $consultations = $_plage->loadRefsConsultations();

            foreach ($consultations as $_consultation) {
                $_consultation->loadRefPatient();
                $_consultation->loadRefCategorie();
                $_consultation->loadRefElementPrescription();

                $place = CMbDT::timeCountIntervals($_plage->debut, $_consultation->heure, $_plage->freq);
                for ($i = 0; $i < $_consultation->duree; $i++) {
                    if (isset($_plage->_listPlace[($place + $i)])) {
                        $_plage->_listPlace[($place + $i)]["consultations"][] = $_consultation;
                    }
                }
            }
        }

        $this->renderSmarty(
            'offline_programme_consult',
            [
                'period_count'    => $period_count,
                'period_type'     => $period_type,
                'date_min'        => $date_min,
                'date_max'        => $date_max,
                'praticiens'      => $praticiens,
                'plageconsult_id' => null,
                'listPlages'      => $listPlages,
                'totals'          => $totals,
                'bank_holidays'   => $bank_holidays,
                'online'          => false,
            ]
        );
    }
}
