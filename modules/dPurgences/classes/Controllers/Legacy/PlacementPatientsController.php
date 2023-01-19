<?php

/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Core\Module\CModule;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Urgences\CRPU;
use Ox\Mediboard\Urgences\Services\PlacementPatientsService;

class PlacementPatientsController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function patientsPlacementView(): void
    {
        $this->checkPermRead();

        $sejour_id   = CView::get("sejour_id", "ref class|CSejour");
        $name_grille = CView::get("name_grille", "str");
        $zone_id     = CView::get("zone_id", "ref class|CChambre");

        CView::checkin();

        $date           = CMbDT::dateTime();
        $date_tolerance = CAppUI::conf("dPurgences date_tolerance");
        $date_before    = CMbDT::date("-$date_tolerance DAY", $date);
        $date_after     = CMbDT::date("+1 DAY", $date);

        $group = CGroups::get();

        $placement_patients = new PlacementPatientsService();

        $services_id = [
            "uhcd"     => null,
            "urgences" => null,
        ];
        $sejours     = [];
        if ($sejour_id) {
            $sejour              = new CSejour();
            $sejours[$sejour_id] = $sejour->load($sejour_id);

            CAccessMedicalData::logAccess($sejour);
        } else {
            //recherche des chambres d'urgences placées
            $chambres_urgences = $placement_patients->getEmergencyRooms();
            $chambres_uhcd     = $placement_patients->getEmergencyRooms(true);

            $_chambres = $chambres_urgences;
            foreach ($chambres_uhcd as $_chambre_uhcd) {
                $_chambres[$_chambre_uhcd->_id] = $_chambre_uhcd;
            }
            $lits = CStoredObject::massLoadBackRefs($_chambres, "lits");

            $ljoin                         = [];
            $ljoin["rpu"]                  = "rpu.sejour_id = sejour.sejour_id";
            $where                         = [];
            $where["sejour.entree"]        = " BETWEEN '$date_before' AND '$date_after'";
            $where["sejour.sortie_reelle"] = "IS NULL";
            $where["sejour.annule"]        = " = '0'";
            $where["sejour.group_id"]      = "= '" . $group->_id . "'";

            $temp = "";
            if (CAppUI::conf("dPurgences create_affectation")) {
                $ljoin["affectation"] = "affectation.sejour_id = sejour.sejour_id";
                $ljoin["service"]     = "service.service_id = affectation.service_id";
                $ljoin["lit"]         = "lit.lit_id = affectation.lit_id";
                $ljoin["chambre"]     = "chambre.chambre_id = lit.chambre_id";

                $where[] = "'$date' BETWEEN affectation.entree AND affectation.sortie";
                if (!CAppUI::conf("dPurgences view_rpu_uhcd")) {
                    $temp = "service.urgence = '1' OR service.radiologie = '1'";
                }
                $where["chambre.chambre_id"] = CSQLDataSource::prepareIn(array_keys($_chambres));
            } else {
                $where["rpu.box_id"] = CSQLDataSource::prepareIn(array_keys($lits));
            }

            if (!CAppUI::conf("dPurgences create_sejour_hospit")) {
                $where[] = "rpu.mutation_sejour_id IS NULL";
            }

            if (!CAppUI::conf("dPurgences view_rpu_uhcd")) {
                $where["sejour.UHCD"] = " = '0'";
            }

            $where_temp = $where;
            if ($temp != "") {
                $where_temp[] = $temp;
            }
            $sejours_chambre = [];
            $sejour          = new CSejour();
            /** @var CSejour[] $sejours */
            $sejours = $sejour->loadList($where_temp, null, null, "sejour_id", $ljoin, "entree");

            if (!CAppUI::conf("dPurgences view_rpu_uhcd")) {
                $where["sejour.UHCD"] = " = '1'";
                $sejours_uhcd         = $sejour->loadList($where, null, null, "sejour_id", $ljoin, "entree");
                foreach ($sejours_uhcd as $sejour_uhcd) {
                    $sejours[$sejour_uhcd->_id] = $sejour_uhcd;
                }
            }

            $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
            CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
            CStoredObject::massLoadFwdRef($sejours, "praticien_id");

            $service = new CService();
            $where   = [
                "urgence"   => " = '1'",
                "cancelled" => " = '0'",
            ];
            $service->loadObject($where);
            $services_id["urgences"] = $service->_id;
            $where                   = [
                "UHCD"      => " = '1'",
                "cancelled" => " = '0'",
            ];
            $service->loadObject($where);
            $services_id["uhcd"] = $service->_id;
        }

        CMbObject::massCountDocItems($sejours);

        CSejour::massLoadNDA($sejours);

        CStoredObject::massLoadBackRefs($sejours, 'consultations', "date DESC, heure DESC", [], [
            "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"
        ]);

        /* Massload of all the affectations of the sejours because they are load in CRPU::updateFormFields */
        CStoredObject::massLoadBackRefs($sejours, 'affectations');
        $affectations = CSejour::massLoadCurrAffectation($sejours);

        $rpus = CStoredObject::massLoadBackRefs($sejours, 'rpu');

        CStoredObject::massLoadFwdRef($rpus, 'motif_sfmu');
        CStoredObject::massLoadBackRefs($rpus, 'notes');
        CRPU::massLoadRefsAttentes($rpus);
        $reservations = CStoredObject::massLoadBackRefs($rpus, 'reservation_rpu');
        CStoredObject::massLoadFwdRef($reservations, 'lit_id');

        $mediusers = CStoredObject::massLoadFwdRef($affectations, 'praticien_id');
        $mediusers = array_replace(CStoredObject::massLoadFwdRef($rpus, 'ide_responsable_id'), $mediusers);
        $mediusers = array_replace(CStoredObject::massLoadFwdRef($sejours, 'praticien_id'), $mediusers);
        CStoredObject::massLoadFwdRef($mediusers, 'function_id');

        $prescriptions = CSejour::massLoadRefPrescriptionSejour($sejours);

        if (HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler')) {
            CPrescription::massCountAlertsNotHandled($prescriptions, 'medium');
            CPrescription::massCountAlertsNotHandled($prescriptions, 'high');
        }

        foreach ($sejours as $sejour) {
            $sejour->loadRefPatient()->updateBMRBHReStatus($sejour);
            $sejour->loadRefPraticien();
            $sejour->_ref_curr_affectation->loadRefService();
            $sejour->countDocItems();
            if (!$sejour->loadRefRPU()->_id) {
                $sejour->_ref_rpu = $sejour->loadUniqueBackRef("rpu_mute");
                if (!$sejour->_ref_rpu) {
                    $sejour->_ref_rpu = new CRPU();
                }
            }
            $rpu = $sejour->_ref_rpu;
            $rpu->loadRefMotifSFMU();
            $rpu->loadRefsNotes();
            $rpu->getColorCIMU();
            $rpu->loadRefIDEResponsable()->loadRefFunction();
            $rpu->loadRefsLastAttentes();
            $reservation  = $rpu->loadRefReservation();
            $prescription = $sejour->_ref_prescription_sejour;

            if ($prescription->_id) {
                if (HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler')) {
                    $prescription->_count_fast_recent_modif = $prescription->_count_alertes;
                    $prescription->_count_urgence["all"]    = $prescription->_count_urgences;
                } else {
                    $prescription->countFastRecentModif();
                    $prescription->loadRefsLinesMedByCat();
                    $prescription->loadRefsLinesElementByCat();
                    $prescription->countUrgence(CMbDT::date($date));
                }
            }
            $chambre_id = $sejour->_ref_curr_affectation->loadRefLit()->chambre_id;
            if (!$chambre_id && !CAppUI::conf("dPurgences create_affectation")) {
                $lit = new CLit();
                $lit->load($sejour->_ref_rpu->box_id);
                $chambre_id = $lit->chambre_id;
            }
            $sejours_chambre[$chambre_id][] = $sejour;

            if ($sejour->_ref_rpu->_id && $reservation->_id && $sejour->_ref_rpu->box_id != $reservation->lit_id) {
                $chambre_resa                     = $reservation->loadRefLit()->chambre_id;
                $sejours_chambre[$chambre_resa][] = $sejour;
            }

            // Le chargement du rpu écrase le chargement de l'affectation courante
            $sejour->_ref_curr_affectation->loadRefPraticien()->loadRefFunction();
        }

        CPrescription::massLoadLinesElementImportant(
            array_combine(
                CMbArray::pluck($sejours, "_ref_prescription_sejour", "_id"),
                CMbArray::pluck($sejours, "_ref_prescription_sejour")
            )
        );

        // Mass loading des catégories sur les rpu
        $rpus = [];
        foreach ($sejours as $_sejour) {
            if (!$_sejour->_ref_rpu->_id) {
                continue;
            }
            $rpus[$_sejour->_ref_rpu->_id] = $_sejour->_ref_rpu;
        }

        CRPU::massLoadCategories($rpus);

        if ($sejour_id) {
            $zone = new CChambre();
            $zone->load($zone_id);

            $this->renderSmarty(
                'inc_patient_placement',
                [
                    "isImedsInstalled" => (CModule::getActive("dPImeds") && CImeds::getTagCIDC($group)),
                    "date"             => $date,
                    "_sejour"          => $sejour,
                    "name_grille"      => $name_grille,
                    "_zone"            => $zone,
                    "with_div"         => 0,
                ]
            );

            return;
        }

        $grilles       = $listSejours = $lits_occupe = [];
        $name_services = [];
        $topologie     = [
            "urgence" => $chambres_urgences,
            "uhcd"    => $chambres_uhcd,
        ];
        if (!CAppUI::gconf("dPurgences Placement superposition_service")) {
            $topologie = [];
            foreach ($chambres_urgences as $_chambre_urg) {
                $topologie[$_chambre_urg->service_id][$_chambre_urg->_id] = $_chambre_urg;
                if (!isset($name_services[$_chambre_urg->service_id])) {
                    $name_services[$_chambre_urg->service_id] = $_chambre_urg->loadRefService()->_view;
                }
            }
            $topologie["uhcd"] = $chambres_uhcd;
        }

        // Add affectations which are not linked to a stay (e.g. blocked bedroom)
        // Go through services, load affectations which have a stay id === null
        $topologie = $placement_patients->addBlockedBedRooms('urgence', $topologie);
        $topologie = $placement_patients->addBlockedBedRooms('uhcd', $topologie);

        $exist_plan = [];
        foreach ($topologie as $nom => $chambres) {
            $exist_plan["$nom"] = count($chambres);
            CService::vueTopologie($chambres, $grilles[$nom], $listSejours[$nom], $sejours_chambre, $lits_occupe[$nom]);
        }

        $this->renderSmarty(
            'vw_placement_patients',
            [
                "isImedsInstalled"                 => (CModule::getActive("dPImeds") && CImeds::getTagCIDC($group)),
                "date"                             => $date,
                "listSejours"                      => $listSejours,
                "lits_occupe"                      => $lits_occupe,
                "grilles"                          => $grilles,
                "suiv"                             => CMbDT::date("+1 day", $date),
                "prec"                             => CMbDT::date("-1 day", $date),
                "exist_plan"                       => $exist_plan,
                "services_id"                      => $services_id,
                "name_services"                    => $name_services,
                "avis_maternite_refresh_frequency" => CAppUI::conf("dPurgences avis_maternite_refresh_frequency"),
            ]
        );
    }
}
