<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Ssr\CActeCsARR;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CPlageGroupePatient;

CCanDo::checkEdit();
$manage_patients = CView::post("manage_patients", "str");
$plage_date      = CView::post("plage_date", "date");
CView::checkin();

$manage_patients = json_decode(utf8_encode(stripslashes($manage_patients)), true);

$plage_groupe_patient = new CPlageGroupePatient();
$plage_groupe_patient->load($manage_patients['plage_groupe_patient_id']);
$evenements_ssr = $plage_groupe_patient->loadRefEvenementsSSR();

$first_day_of_week = CMbDT::date("$plage_groupe_patient->groupe_day this week", $plage_date);

$days = [];

foreach ($manage_patients['sejours'] as $_sejour_id => $actes_csarr) {
    $sejour = new CSejour();
    $sejour->load($_sejour_id);

    // Dates des séances jusqu'à la fin du séjour
    $days = $plage_groupe_patient->calculateDatesForPlageGroup($sejour, $first_day_of_week);

    if ($actes_csarr["checked"]) {
        // Creating patient events and acts
        foreach ($actes_csarr as $_acte) {
            if (is_array($_acte)) {
                // Get the prescription line element
                $element_id = $_acte["element_prescription_id"];
                $event_id   = $_acte["event_id"];

                $ljoin    = [
                    "prescription" => "prescription.prescription_id = prescription_line_element.prescription_id",
                ];
                $where    = [
                    "prescription.type"                                 => " = 'sejour'",
                    "prescription.object_id"                            => " = '$sejour->_id'",
                    "prescription.object_class"                         => " = 'CSejour'",
                    "prescription_line_element.element_prescription_id" => " = '$element_id'",
                    "prescription_line_element.active"                  => " = '1'",
                ];
                $line_elt = new CPrescriptionLineElement();
                $line_elt->loadObject(
                    $where,
                    "debut asc",
                    "prescription_line_element.prescription_line_element_id",
                    $ljoin
                );

                if ($line_elt->_id) {
                    // Création des événements jusqu'à la fin du séjour
                    foreach ($days as $_day) {
                        $datetime_debut = $_day . " " . $_acte["acte_heure_debut"];

                        // Ne pas dépasser la fin réelle de la ligne d'élément
                        if ($_day > CMbDT::date($line_elt->_fin_reelle)) {
                            continue;
                        }

                        $event_ssr                               = new CEvenementSSR();
                        $event_ssr->sejour_id                    = $sejour->_id;
                        $event_ssr->plage_groupe_patient_id      = $plage_groupe_patient->_id;
                        $event_ssr->prescription_line_element_id = $line_elt->_id;
                        $event_ssr->duree                        = $_acte["duree"];
                        $event_ssr->type_seance                  = $_acte["type_seance"] ?: "dediee";
                        $event_ssr->therapeute_id                = $_acte["executant"];
                        $event_ssr->debut                        = $datetime_debut;
                        $event_ssr->realise                      = 0;
                        $event_ssr->annule                       = 0;
                        $event_ssr->loadMatchingObject();

                        if (!$event_ssr->_id && !$event_id) {
                            if ($msg = $event_ssr->store()) {
                                CAppUI::setMsg($msg, UI_MSG_ERROR);
                                continue;
                            }
                            CAppUI::displayMsg($msg, "CEvenementSSR-msg-create");
                            // Ajout des actes
                            $acte_csarr                   = new CActeCsARR();
                            $acte_csarr->evenement_ssr_id = $event_ssr->_id;
                            $acte_csarr->sejour_id        = $sejour->_id;
                            $acte_csarr->code             = $_acte["code"];
                            $acte_csarr->_modulateurs     = explode("|", $_acte["modulateurs"]);
                            $acte_csarr->extension        = $_acte["extension"];

                            $msg = $acte_csarr->store();
                            CAppUI::displayMsg($msg, "$acte_csarr->_class-msg-create");
                        } // Edit event
                        else {
                            CEvenementSSR::editSSREventsForGroupRange($sejour, $datetime_debut, $event_ssr, $_acte);
                        }
                    }
                }
            }
        }
    } // Delete the patient of the group range if uncheck
    elseif ($actes_csarr["already_range"]) {
        $where = [
            "plage_groupe_patient_id" => " = '$plage_groupe_patient->_id'",
            "debut"                   => " >= '$first_day_of_week $plage_groupe_patient->heure_debut'",
            "realise"                 => " = '0'",
            "annule"                  => " = '0'",
        ];

        $events_ssr = $sejour->loadRefsEvtsSSRSejour($where);

        foreach ($events_ssr as $_event) {
            if ($msg = $_event->delete()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
                continue;
            }
            CAppUI::displayMsg($msg, "CEvenementSSR-msg-delete");
        }
    }
}

echo CAppUI::getMsg();
