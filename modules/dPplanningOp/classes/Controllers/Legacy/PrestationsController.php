<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CItemLiaison;
use Ox\Mediboard\Hospi\CPrestationJournaliere;
use Ox\Mediboard\Hospi\CPrestationPonctuelle;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanningOp\Services\PrestationsService;

class PrestationsController extends CLegacyController
{
    public function viewPrestations(): void
    {
        $this->checkPermRead();

        $sejour_id          = CView::get("sejour_id", "ref class|CSejour");
        $relative_date      = CView::get("relative_date", "date");
        $with_buttons       = CView::get("with_buttons", "bool default|1");
        $is_contextual_call = CView::get("is_contextual_call", "bool default|0");

        CView::checkin();

        $sejour = new CSejour();
        $sejour->load($sejour_id);

        CAccessMedicalData::logAccess($sejour);

        $prestations_j = CPrestationJournaliere::loadCurrentList($sejour->type, $sejour->type_pec);

        $dates              = [];
        $prestations_p      = [];
        $liaisons_j         = [];
        $liaisons_p         = [];
        $liaisons_p_forfait = [];
        $date_modif         = [];
        $save_state         = [];

        $sejour->loadRefPrescriptionSejour();
        $sejour->loadRefCurrAffectation()->updateView();
        $sejour->loadRefsOperations();

        $dossier_medical_sejour = $sejour->loadRefDossierMedical();
        $dossier_medical_sejour->loadRefsAntecedents();

        $patient = $sejour->loadRefPatient();
        $patient->loadRefPhotoIdentite();
        $patient->loadRefLatestConstantes();

        $dossier_medical = $patient->loadRefDossierMedical();
        $dossier_medical->loadRefsAntecedents();
        $dossier_medical->loadRefsAllergies();
        $dossier_medical->countAntecedents();
        $dossier_medical->countAllergies();

        $where_actif = ["actif" => "= '1'"];

        $items = CStoredObject::massLoadBackRefs($prestations_j, "items", "rank", $where_actif);
        CStoredObject::massLoadBackRefs($items, "sous_items", "nom", $where_actif);

        foreach ($prestations_j as $_prestation_id => $_prestation) {
            $items = $_prestation->loadRefsItems($where_actif);
            foreach ($items as $_item) {
                $_item->loadRefsSousItems($where_actif);
            }
        }

        // Droits de modification
        $editRights = CModule::getCanDo("dPhospi")->edit;

        $duree = CMbDT::daysRelative($sejour->entree, $sejour->sortie);

        $date_temp = CMbDT::date($sejour->entree);

        while ($date_temp <= CMbDT::date($sejour->sortie)) {
            $dates[$date_temp] = $date_temp;
            $date_temp         = CMbDT::date("+1 day", $date_temp);
        }

        // Gestion des liaisons hors séjour
        $dates_after = [];

        /** @var CItemLiaison[] $items_liaisons */
        $items_liaisons = $sejour->loadRefItemsLiaisons();
        CStoredObject::massLoadFwdRef($items_liaisons, "item_souhait_id");
        CStoredObject::massLoadFwdRef($items_liaisons, "item_realise_id");
        CStoredObject::massLoadFwdRef($items_liaisons, "sous_item_id");

        foreach ($items_liaisons as $_item_liaison) {
            if (!$_item_liaison->date) {
                $liaisons_p_forfait[$_item_liaison->item_souhait_id] = $_item_liaison->_id;
                continue;
            }

            if ($_item_liaison->date > CMbDT::date($sejour->sortie)) {
                $dates_after[$_item_liaison->date] = $_item_liaison->date;
            }

            $item_souhait = $_item_liaison->loadRefItem();
            $item_realise = $_item_liaison->loadRefItemRealise();
            $_item_liaison->loadRefSousItem();

            $object_class = $_item_liaison->prestation_id ? "CPrestationJournaliere" : "CPrestationPonctuelle";

            switch ($object_class) {
                case "CPrestationJournaliere":
                default:
                    $liaisons_j[$_item_liaison->date][$_item_liaison->prestation_id] = $_item_liaison;

                    if (!isset($prestations_j[$_item_liaison->prestation_id])) {
                        $prestation = new CPrestationJournaliere();
                        $prestation->load($_item_liaison->prestation_id);
                        $prestation->loadRefsItems();
                        $prestations_j[$_item_liaison->prestation_id] = $prestation;
                    }

                    if ($item_souhait->_id && !isset($prestations_j[$item_souhait->object_id]->_ref_items[$item_souhait->_id])) {
                        $prestations_j[$item_souhait->object_id]->_ref_items[$item_souhait->_id] = $item_souhait;
                        $item_souhait->loadRefsSousItems();
                    }
                    if ($item_realise->_id && !isset($prestations_j[$item_realise->object_id]->_ref_items[$item_realise->_id])) {
                        $prestations_j[$item_realise->object_id]->_ref_items[$item_realise->_id] = $item_realise;
                    }
                    if ($_item_liaison->sous_item_id) {
                        $sous_item                                                                                                      = $_item_liaison->_ref_sous_item;
                        $item                                                                                                           = $sous_item->loadRefItemPrestation(
                        );
                        $prestations_j[$item->object_id]->_ref_items[$sous_item->item_prestation_id]->_refs_sous_items[$sous_item->_id] = $sous_item;
                    }
                    break;
                case "CPrestationPonctuelle":
                    $liaisons_p[$_item_liaison->date][$_item_liaison->_ref_item->object_id][] = $_item_liaison;

                    if (!isset($prestations_p[$item_souhait->object_id])) {
                        $prestation = new CPrestationPonctuelle();
                        $prestation->load($item_souhait->object_id);
                        $prestation->loadRefsItems();
                        $prestations_p[$item_souhait->object_id] = $prestation;
                    }
            }
        }

        $prestations_service = new PrestationsService();

        foreach ($dates as $_date) {
            if (!isset($liaisons_j[$_date])) {
                $liaisons_j[$_date] = [];
            }

            foreach ($prestations_j as $_prestation_id => $_prestation) {
                $item_liaison      = new CItemLiaison();
                $item_liaison->_id = "temp";
                $item_liaison->loadRefItem();
                $item_liaison->loadRefItemRealise();
                $item_liaison->loadRefSousItem();

                if (isset($liaisons_j[$_date][$_prestation_id])) {
                    $date_modif[$_date] = 1;
                    $prestations_service->copyLiaison($item_liaison, $liaisons_j[$_date][$_prestation_id]);

                    $save_state[$_prestation_id] = $item_liaison;
                } elseif (isset($save_state[$_prestation_id])) {
                    $prestations_service->copyLiaison($item_liaison, $save_state[$_prestation_id]);

                    $liaisons_j[$_date][$_prestation_id] = $item_liaison;
                }
            }
        }

        $empty_liaison      = new CItemLiaison();
        $empty_liaison->_id = "temp";
        $empty_liaison->loadRefItem();
        $empty_liaison->loadRefItemRealise();

        // La date pour l'ajout d'une prestation ponctuelle doit être dans les dates du séjour
        // Si la date actuelle est hors des bornes, alors réinitialisation à la date d'entrée du séjour
        $today_ponctuelle = CMbDT::date();
        if ($today_ponctuelle < CMbDT::date($sejour->entree) || $today_ponctuelle > CMbDT::date($sejour->sortie)) {
            $today_ponctuelle = CMbDT::date($sejour->entree);
        }

        // Prestation ponctuelles au forfait
        $prestations_p_forfait = CPrestationPonctuelle::loadCurrentListForfait($sejour->type, $sejour->type_pec);

        CStoredObject::massLoadBackRefs($prestations_p_forfait, "items");
        foreach ($prestations_p_forfait as $_prestation) {
            $_prestation->loadRefsItems();
        }

        // Limite des jours affichés à -30 / +30
        $now = CMbDT::date();

        if (count($dates) >= 60) {
            if (in_array($now, $dates) || ($sejour->entree > CMbDT::date())) {
                $keys_date   = array_keys($dates);
                $offset_date = array_search($now, $keys_date);

                // On remet l'offset à 0 si le séjour a commencé il y a moins de 30 jours
                if (
                    $sejour->entree < CMbDT::date()
                    && (CMbDT::daysRelative($sejour->entree, CMbDT::date()) < 30)
                ) {
                    $offset_date = 0;
                }

                // Si on est à plus de 30 cases du tableau, on peut retirer les 30
                if ($offset_date > 30) {
                    $offset_date -= 30;
                }

                $dates = array_slice($dates, $offset_date, 60, true);
            } else {
                // Les 60 dernières cases si le séjour est déjà fini
                $dates = array_slice($dates, -60, 60, true);
            }
        }

        $this->renderSmarty(
            'inc_vw_prestations',
            [
                "today_ponctuelle"      => $today_ponctuelle,
                "dates"                 => $dates,
                "dates_after"           => $dates_after,
                "relative_date"         => $relative_date,
                "sejour"                => $sejour,
                "prestations_j"         => $prestations_j,
                "prestations_p"         => $prestations_p,
                "empty_liaison"         => $empty_liaison,
                "liaisons_p"            => $liaisons_p,
                "liaisons_j"            => $liaisons_j,
                "liaisons_p_forfait"    => $liaisons_p_forfait,
                "date_modified"         => $date_modif,
                "editRights"            => $editRights,
                "bank_holidays"         => CMbDT::getHolidays(CMbDT::date()),
                "prestations_p_forfait" => $prestations_p_forfait,
                "with_buttons"          => $with_buttons,
                "is_contextual_call"    => $is_contextual_call,
            ]
        );
    }
}
