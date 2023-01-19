<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Ssr\CActeCsARR;
use Ox\Mediboard\Ssr\CActePrestationSSR;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CPlageSeanceCollective;

class CollectiveEvenementSSRController extends CLegacyController
{
    /**
     * Manage the collective SSR event planning
     *
     * @return void
     * @throws Exception
     */
    public function manageCollectiveSSREventsPlanning(): void
    {
        $this->checkPermRead();

        global $g, $m;

        $sejour_ids = CView::post("sejour_ids", "str");
        $plage_id   = CView::post("plage_id", "ref class|CPlageSeanceCollective");
        $sejour_ids = json_decode(utf8_encode(stripslashes($sejour_ids)), true);

        $plage = CPlageSeanceCollective::findOrNew($plage_id);
        $plage->loadRefsActes();
        $now               = CMbDT::date();
        $first_day_of_week = CMbDT::date("$plage->day_week this week");
        if ($now > $first_day_of_week) {
            $first_day_of_week = CMbDT::date("+1 week", $first_day_of_week);
        }

        foreach ($sejour_ids as $sejour_id => $_sejour) {
            $sejour = CSejour::findOrNew($sejour_id);
            $date_sortie         = CMbDT::date($sejour->sortie);
            $date_entree         = CMbDT::date($sejour->entree);
            $first_day_of_sejour = $first_day_of_week > $date_entree ? $first_day_of_week : $date_entree;
            $days                = [];
            for ($day = $first_day_of_sejour; $day <= $date_sortie; $day = CMbDT::date("+1 week", $day)) {
                $days[$day] = $day;
            }
            $where = [
                "sejour_id"             => " = '$sejour_id'",
                "type_seance"           => " = 'collective'",
                "seance_collective_id"  => " IS NOT NULL",
                "evenement_ssr.realise" => "= '0'",
                "plage_id"              => " = '$plage_id'",
                "DATE(evenement_ssr.debut) " . CSQLDataSource::prepareIn(array_keys($days)),
            ];

            $evt  = new CEvenementSSR();
            $evts = $evt->loadList($where, "debut");

            if ($_sejour["checked"]) {
                // Préparation de la suppression des autres avenements en collision
                $evt            = new CEvenementSSR();
                $evt->sejour_id = $sejour_id;
                $evt->debut     = $plage->debut;
                $evt->duree     = $plage->duree;

                //Récupération de la ligne d'élément de prescription
                $ljoin    = [
                    "prescription" => "prescription.prescription_id = prescription_line_element.prescription_id",
                ];
                $where    = [
                    "prescription.type"                                 => " = 'sejour'",
                    "prescription.object_id"                            => " = '$sejour_id'",
                    "prescription.object_class"                         => " = 'CSejour'",
                    "prescription_line_element.element_prescription_id" => " = '$plage->element_prescription_id'",
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
                    //Recupération des éléments paramétrés
                    $csarr_codes_elt = $line_elt->loadRefElement()->loadRefsCsarrs();

                    $csarr_activites = [];
                    foreach ($csarr_codes_elt as $_code_elt) {
                        $csarr_activites[$_code_elt->code] = $_code_elt;
                    }

                    //Création des événements jusqu'à la fin du séjour
                    foreach ($days as $_day) {
                        $evt_prioritaire        = new CEvenementSSR();
                        $evt_prioritaire->debut = $_day . " " . $plage->debut;
                        $evt_prioritaire->duree = $plage->duree;
                        // Vérification qu'il n'y ait pas de collisions avec un événement plus prioritaire
                        $evts_prioritaire = $evt_prioritaire->getCollectivesCollisions(
                            null,
                            $plage->niveau,
                            $sejour_id,
                            "<",
                            false
                        );
                        if (count($evts_prioritaire) > 0) {
                            continue;
                        }
                        $evt->deleteCollectivesByPlage([$_day], $plage->niveau);

                        //Recherche de la plage collective
                        $where                = [];
                        $where[]              = "DATE(debut) = '$_day'";
                        $where["plage_id"]    = " = '$plage_id'";
                        $where["type_seance"] = " = 'collective'";
                        $where[]              = "seance_collective_id IS NULL";
                        $evt_collectif        = new CEvenementSSR();
                        $evt_collectif->loadObject($where);
                        //Création de la plage collective si besoin
                        if (!$evt_collectif->_id) {
                            $evt_collectif                 = new CEvenementSSR();
                            $evt_collectif->plage_id       = $plage_id;
                            $evt_collectif->debut          = $_day . " " . $plage->debut;
                            $evt_collectif->duree          = $plage->duree;
                            $evt_collectif->therapeute_id  = $plage->user_id;
                            $evt_collectif->therapeute2_id = $plage->therapeute2_id;
                            $evt_collectif->therapeute3_id = $plage->therapeute3_id;
                            $evt_collectif->type_seance    = "collective";
                            $evt_collectif->equipement_id  = $plage->equipement_id;
                            if ($msg = $evt_collectif->store()) {
                                CAppUI::setMsg($msg, UI_MSG_ERROR);
                                continue;
                            }
                        }

                        //Création de l'événement du patient
                        $evt                               = new CEvenementSSR();
                        $evt->sejour_id                    = $sejour_id;
                        $evt->plage_id                     = $plage_id;
                        $evt->debut                        = $_day . " " . $plage->debut;
                        $evt->duree                        = $plage->duree;
                        $evt->therapeute_id                = $plage->user_id;
                        $evt->therapeute2_id               = $plage->therapeute2_id;
                        $evt->therapeute3_id               = $plage->therapeute3_id;
                        $evt->prescription_line_element_id = $line_elt->_id;
                        $evt->seance_collective_id         = $evt_collectif->_id;
                        $evt->type_seance                  = "collective";
                        $evt->loadMatchingObject();
                        if (!$evt->_id) {
                            if ($msg = $evt->store()) {
                                CAppUI::setMsg($msg, UI_MSG_ERROR);
                                continue;
                            }
                            CAppUI::displayMsg($msg, "CEvenementSSR-msg-create");
                            //Ajout des actes
                            foreach ($plage->_ref_actes_by_type as $_type_acte => $_actes) {
                                foreach ($_actes as $_acte_plage) {
                                    switch ($_type_acte) {
                                        case "csarr":
                                            $new_acte               = new CActeCsARR();
                                            $new_acte->commentaire  = isset($csarr_activites[$_acte_plage->code]) ? $csarr_activites[$_acte_plage->code]->commentaire : null;
                                            $new_acte->_modulateurs = isset($csarr_activites[$_acte_plage->code]) && $csarr_activites[$_acte_plage->code]->modulateurs ? explode(
                                                "|",
                                                $csarr_activites[$_acte_plage->code]->modulateurs
                                            ) : null;
                                            $new_acte->extension    = isset($csarr_activites[$_acte_plage->code]) ? $csarr_activites[$_acte_plage->code]->code_ext_documentaire : null;
                                            break;
                                        case "presta":
                                        default:
                                            $new_acte       = new CActePrestationSSR();
                                            $new_acte->type = "presta_ssr";
                                            break;
                                    }
                                    $new_acte->code             = $_acte_plage->code;
                                    $new_acte->quantite         = $_acte_plage->quantite;
                                    $new_acte->evenement_ssr_id = $evt->_id;
                                    $new_acte->sejour_id        = $sejour_id;
                                    $msg                        = $new_acte->store();
                                    CAppUI::displayMsg($msg, "$new_acte->_class-msg-create");
                                }
                            }
                        }
                    }
                }
            } elseif (count($evts)) {
                foreach ($evts as $_evt) {
                    if ($_evt->realise) {
                        continue;
                    }
                    if ($msg = $_evt->delete()) {
                        CAppUI::setMsg($msg, UI_MSG_ERROR);
                        continue;
                    }
                    CAppUI::displayMsg($msg, "CEvenementSSR-msg-delete");
                }
            }
        }

        echo CAppUI::getMsg();
        $this->rip();
    }
}
