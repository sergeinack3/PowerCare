<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CItemPrestation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Charge complètement un service pour l'affichage des affectations
 *
 * @param CService $service       le service concerné
 * @param string   $date          le filtre de date sur les affectations
 * @param string   $mode          forcer le chargement des affectations effectuées
 * @param string   $praticien_id  charge les séjours pour un praticien en particulier
 * @param string   $type          charge les séjours pour un type d'hospitalisation
 * @param string   $prestation_id charge la prestation éventuellement associée à chaque séjour
 *
 * @return void
 */
function loadServiceComplet(&$service, $date, $mode, $praticien_id = "", $type = "", $prestation_id = "", $with_dossier_medical = true)
{
    $service->_nb_lits_dispo = 0;
    $dossiers                = [];
    $systeme_presta          = CAppUI::gconf("dPhospi prestations systeme_prestations");

    /** @var CLit[] $lits */
    $lits = $service->loadRefsLits();

    foreach ($lits as $_lit) {
        $_lit->_ref_affectations = [];
    }

    CLit::massCheckDispo($lits, $date);

    $affectations = $service->loadRefsAffectations($date, $mode, false, true);

    $sejours        = CStoredObject::massLoadFwdRef($affectations, "sejour_id");
    $praticiens_aff = CStoredObject::massLoadFwdRef($affectations, "praticien_id");
    CStoredObject::massLoadFwdRef($praticiens_aff, "function_id");
    $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
    CStoredObject::massLoadFwdRef($sejours, "prestation_id");
    CStoredObject::massLoadFwdRef($sejours, "praticien_id");
    CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC");
    CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

    if (CModule::getActive("dPImeds")) {
        CSejour::massLoadNDA($sejours);
    }

    if ($with_dossier_medical) {
        CStoredObject::massLoadBackRefs($patients, "dossier_medical");
    }

    $use_prat_aff = CAppUI::gconf("dPplanningOp CSejour use_prat_aff");

    foreach ($affectations as $_affectation) {
        $_affectation->loadRefPraticien()->loadRefFunction();
        $sejour = $_affectation->loadRefSejour();
        $sejour->loadRefsOperations();

        if ($praticien_id) {
            if ($use_prat_aff) {
                if (($_affectation->praticien_id != $praticien_id) && ($sejour->praticien_id != $praticien_id)) {
                    unset($affectations[$_affectation->_id]);
                    continue;
                }
            } else {
                if ($sejour->praticien_id != $praticien_id) {
                    unset($affectations[$_affectation->_id]);
                    continue;
                }
            }
        }
        if ($type) {
            if ($sejour->type != $type) {
                unset($affectations[$_affectation->_id]);
                continue;
            }
        }

        if (array_key_exists($_affectation->lit_id, $lits)) {
            $lits[$_affectation->lit_id]->_ref_affectations[$_affectation->_id] = $_affectation;
        }

        $_affectation->loadRefsAffectations(true);

        $_affectation->checkDaysRelative($date);

        $aff_prev = $_affectation->_ref_prev;
        if ($aff_prev->_id) {
            if ($aff_prev->lit_id) {
                $aff_prev->loadRefLit();
            } else {
                $aff_prev->loadRefService();
            }
        }

        $aff_next = $_affectation->_ref_next;
        if ($aff_next->_id) {
            if ($aff_next->lit_id) {
                $aff_prev->loadRefLit();
            } else {
                $aff_prev->loadRefService();
            }
        }

        $sejour->loadRefPrestation();
        $sejour->loadRefsOperations();
        $sejour->loadRefPraticien();
        $sejour->loadRefPatient()->loadRefsPatientHandicaps();

        if ($with_dossier_medical) {
            $sejour->_ref_patient->loadRefDossierMedical(false);
            $dossiers[] = $sejour->_ref_patient->_ref_dossier_medical;
        }

        // Chargement des droits C2S
        $sejour->getDroitsC2S();

        foreach ($sejour->_ref_operations as $_operation) {
            $_operation->loadExtCodesCCAM();
        }

        $_affectation->_ref_lit = $lits[$_affectation->lit_id];

        $_affectation->loadRefLit();

        $_affectation
            ->_ref_lit
            ->_ref_chambre
            ->_nb_affectations++;

        if ($systeme_presta == "expert" && $prestation_id) {
            $sejour->loadLiaisonsForDay($prestation_id, $date);
            $sejour->loadLiaisonsPonctualPrestationsForDay($date);
        }

        $sejour->loadRefPatient()->updateBMRBHReStatus($sejour);
    }

    foreach ($lits as $_lit) {
        if (count($_lit->_ref_affectations)) {
            $entree_order     = CMbArray::pluck($_lit->_ref_affectations, '_ref_sejour', 'entree');
            $parent_aff_order = CMbArray::pluck($_lit->_ref_affectations, 'parent_affectation_id');
            array_multisort(
                $parent_aff_order,
                SORT_ASC,
                $entree_order,
                SORT_ASC,
                $_lit->_ref_affectations
            );
        }
    }

    if ($with_dossier_medical) {
        CDossierMedical::massCountAntecedentsByType($dossiers, "deficience");
    }

    if (!$service->externe) {
        foreach ($service->_ref_chambres as $_chambre) {
            $_chambre->checkChambre($date, $mode);
            $service->_nb_lits_dispo += $_chambre->_nb_lits_dispo;
        }
    }
}

/**
 *  Chargement des admissions à affecter
 */
function loadSejourNonAffectes($where, $order = null, $praticien_id = null, $prestation_id = null)
{
    $group_id       = CGroups::loadCurrent()->_id;
    $systeme_presta = CAppUI::conf("dPhospi prestations systeme_prestations", "CGroups-$group_id");

    $leftjoin = [
        "affectation" => "sejour.sejour_id = affectation.sejour_id",
    ];

    if ($praticien_id) {
        $where["sejour.praticien_id"] = " = '$praticien_id'";
    }

    $where["sejour.group_id"] = "= '$group_id'";

    $where[] = "(sejour.type != 'seances' && affectation.affectation_id IS NULL) OR sejour.type = 'seances'";

    $sejourNonAffectes = new CSejour();
    $sejourNonAffectes = $sejourNonAffectes->loadList($where, $order, 100, null, $leftjoin);

    CStoredObject::massLoadFwdRef($sejourNonAffectes, "prestation_id");
    CStoredObject::massLoadFwdRef($sejourNonAffectes, "praticien_id");
    $patients = CStoredObject::massLoadFwdRef($sejourNonAffectes, "patient_id");

    CStoredObject::massLoadBackRefs($sejourNonAffectes, "operations", "date ASC");
    CStoredObject::massLoadBackRefs($patients, "dossier_medical");

    CSejour::massLoadNDA($sejourNonAffectes);

    if ($systeme_presta == "expert" && $prestation_id) {
        CSejour::massLoadLiaisonsForPrestation($sejourNonAffectes, $prestation_id);
    }

    CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

    /** @var $sejourNonAffectes CSejour[] */
    foreach ($sejourNonAffectes as $sejour) {
        $sejour->loadRefPrestation();
        $sejour->loadRefPraticien();
        $sejour->loadRefPatient();
        $sejour->loadLastAutorisationPermission();
        $sejour->_ref_patient->loadRefDossierMedical(false);
        $sejour->_ref_patient->updateBMRBHReStatus($sejour);

        // Chargement des droits C2S
        $sejour->getDroitsC2S();

        // Chargement des opérations
        $sejour->loadRefsOperations();
        foreach ($sejour->_ref_operations as $_operation) {
            $_operation->loadExtCodesCCAM();
        }
    }

    $dossiers = CMbArray::pluck($sejourNonAffectes, "_ref_patient", "_ref_dossier_medical");

    CDossierMedical::massCountAntecedentsByType($dossiers, "deficience");

    if ($order === null && count($sejourNonAffectes)) {
        $keys = array_keys($sejourNonAffectes);
        $order_sejour_function = CMbArray::pluck($sejourNonAffectes, "_ref_praticien", "function_id");
        $order_sejour_entree = CMbArray::pluck($sejourNonAffectes, "entree_prevue");
        $order_sejour_pat_nom = CMbArray::pluck($sejourNonAffectes, "_ref_patient", "nom");
        $order_sejour_pat_prenom = CMbArray::pluck($sejourNonAffectes, "_ref_patient", "prenom");
        array_multisort(
            $order_sejour_function,
            SORT_ASC,
            $order_sejour_entree,
            SORT_ASC,
            $order_sejour_pat_nom,
            SORT_ASC,
            $order_sejour_pat_prenom,
            SORT_ASC,
            $sejourNonAffectes,
            $keys
        );

        $sejourNonAffectes = array_combine($keys, $sejourNonAffectes);
    }

    return $sejourNonAffectes;
}

/**
 * Chargement des affectations dans les couloirs
 */
function loadAffectationsCouloirs($where, $order = null, $praticien_id = null, $prestation_id = null)
{
    $group_id       = CGroups::loadCurrent()->_id;
    $systeme_presta = CAppUI::conf("dPhospi prestations systeme_prestations", "CGroups-$group_id");

    $ljoin = [
        "sejour" => "affectation.sejour_id = sejour.sejour_id",
    ];

    if ($praticien_id) {
        $where["sejour.praticien_id"] = " = '$praticien_id'";
    }

    $where["affectation.lit_id"] = " IS NULL";

    $affectation = new CAffectation;
    /* @var CAffectation[] $affectations */
    $affectations = $affectation->loadList($where, $order, null, null, $ljoin);

    $sejours        = CStoredObject::massLoadFwdRef($affectations, "sejour_id");
    $praticiens_aff = CStoredObject::massLoadFwdRef($affectations, "praticien_id");
    CStoredObject::massLoadFwdRef($praticiens_aff, "function_id");

    CStoredObject::massLoadFwdRef($sejours, "prestation_id");
    CStoredObject::massLoadFwdRef($sejours, "praticien_id");
    $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");

    CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC");

    CSejour::massLoadNDA($sejours);

    if ($systeme_presta == "expert" && $prestation_id) {
        CSejour::massLoadLiaisonsForPrestation($sejours, $prestation_id);
    }

    CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

    foreach ($affectations as $affectation) {
        loadAffectation($affectation);
    }
    $dossiers = CMbArray::pluck($affectations, "_ref_sejour", "_ref_patient", "_ref_dossier_medical");
    CDossierMedical::massCountAntecedentsByType($dossiers, "deficience");

    if ($order == null) {
        $order_affectation_function = CMbArray::pluck($affectations, "_ref_sejour", "_ref_praticien", "function_id");
        $order_affectation_entree = CMbArray::pluck($affectations, "_ref_sejour", "entree_prevue");
        $order_affectation_pat_nom = CMbArray::pluck($affectations, "_ref_sejour", "_ref_patient", "nom");
        $order_affectation_pat_prenom = CMbArray::pluck($affectations, "_ref_sejour", "_ref_patient", "prenom");
        array_multisort(
            $order_affectation_function,
            SORT_ASC,
            $order_affectation_entree,
            SORT_ASC,
            $order_affectation_pat_nom,
            SORT_ASC,
            $order_affectation_pat_prenom,
            SORT_ASC,
            $affectations
        );
    }

    return $affectations;
}

function loadVueTempo(
    &$objects,
    $suivi_affectation,
    $lits,
    &$operations,
    $date_min,
    $date_max,
    $period,
    $prestation_id,
    &$functions_filter = null,
    $filter_function = null,
    &$sejours_non_affectes = null
) {

    if (!$objects) {
        $objects = [];
    }

    if (!$operations) {
        $operations = [];
    }

    if (!$lits) {
        $lits = [];
    }

    $maternite_active = CModule::getActive("maternite");

    foreach ($objects as $_object) {
        switch ($_object->_class) {
            case "CAffectation":
                if ($_object->_is_prolong) {
                    $_object->sortie = CMbDT::dateTime();
                }

                $_object->loadRefsAffectations();
                $_object->loadRefFunction();
                $_object->_affectations_enfant_ids = CMbArray::pluck($_object->loadBackRefs("affectations_enfant"), "affectation_id");
                /** @var CSejour $sejour * */
                $sejour = $_object->loadRefSejour();

                if (!$suivi_affectation && $_object->parent_affectation_id) {
                    $suivi_affectation = true;
                }

                if ($_object->lit_id) {
                    $lits[$_object->lit_id]->_ref_affectations[$_object->_id] = $_object;
                }

                if ($_object->_is_prolong) {
                    $_object->_start_prolongation = CMbDT::position(max($date_min, $_object->entree), $date_min, $period);
                    $_object->_end_prolongation   = CMbDT::position(min($date_max, $_object->sortie), $date_min, $period);
                    $_object->_width_prolongation = $_object->_end_prolongation - $_object->_start_prolongation;
                }

                break;
            default:
            case "CSejour":
                $sejour = $_object;
        }

        $sejour->loadRefPraticien()->loadRefFunction();

        if (is_array($functions_filter)) {
            $functions_filter[$sejour->_ref_praticien->function_id] = $sejour->_ref_praticien->_ref_function;
            if ($filter_function && $filter_function != $sejour->_ref_praticien->function_id) {
                unset($objects[$_object->_id]);
                continue;
            }
        }

        $sejour->loadRefPrestation();
        $sejour->loadRefChargePriceIndicator();
        $patient = $sejour->loadRefPatient();
        $patient->loadRefPhotoIdentite();
        $patient->getSurpoids();
        $patient->loadRefDossierMedical(false);
        $patient->loadRefsPatientHandicaps();

        $_object->_entree_offset = CMbDT::position(max($date_min, $_object->entree), $date_min, $period);
        $_object->_sortie_offset = CMbDT::position(min($date_max, $_object->sortie), $date_min, $period);
        $_object->_width         = $_object->_sortie_offset - $_object->_entree_offset;

        if ($_object->_width === 0) {
            $_object->_width = 0.01;
        }

        if (!isset($operations[$sejour->_id])) {
            $operations[$sejour->_id] = $sejour->loadRefsOperations();
        }

        if ($maternite_active && $sejour->grossesse_id) {
            $sejour->_sejours_enfants_ids = CMbArray::pluck($sejour->loadRefsNaissances(), "sejour_enfant_id");
        }

        foreach ($operations[$sejour->_id] as $_operation) {
            $_operation->loadRefPlageOp(1);
            $hour_operation = CMbDT::format($_operation->temp_operation, "%H");
            $min_operation  = CMbDT::format($_operation->temp_operation, "%M");
            $fin_operation  = CMbDT::dateTime("+$hour_operation hours +$min_operation minutes", $_operation->_datetime_best);

            if (!$sejour->_ref_next_operation && $_operation->_datetime_best >= $date_min) {
                $sejour->_ref_next_operation = $_operation;
            }

            if ($_operation->_datetime_best > $date_max || $fin_operation < $date_min || $_operation->_datetime_best < $sejour->entree) {
                unset($sejour->_ref_operations[$_operation->_id]);
                continue;
            }

            $_operation->_debut_offset[$_object->_id] = CMbDT::position($_operation->_datetime_best, max($date_min, $_object->entree), $period);

            $_operation->_fin_offset[$_object->_id] = CMbDT::position($fin_operation, max($date_min, $_object->entree), $period);
            $_operation->_width[$_object->_id]      = $_operation->_fin_offset[$_object->_id] - $_operation->_debut_offset[$_object->_id];

            if (($_operation->_datetime_best > $date_max)) {
                $_operation->_width_uscpo[$_object->_id] = 0;
            } else {
                $fin_uscpo                               = $hour_operation + 24 * $_operation->duree_uscpo;
                $_operation->_width_uscpo[$_object->_id] = CMbDT::position(
                        CMbDT::dateTime("+$fin_uscpo hours + $min_operation minutes", $_operation->_datetime_best),
                        max($date_min, $_object->entree),
                        $period
                    ) - $_operation->_fin_offset[$_object->_id];
            }
        }

        if (is_array($sejours_non_affectes)) {
            $lit                 = new CLit();
            $lit->_selected_item = new CItemPrestation();
            $lit->_lines         = [];
            if ($_object instanceof CAffectation) {
                $lit->_affectation_id = $_object->_id;
                $lit->_lines[]        = $_object->_id;
            } else {
                $lit->_sejour_id = $_object->_id;
                $lit->_lines[]   = $_object->_guid;
            }

            @$sejours_non_affectes[$_object->service_id ? $_object->service_id : "np"][] = $lit;
        }
    }

    $sejours = [];

    foreach ($objects as $_object) {
        switch ($_object->_class) {
            default:
            case "CSejour":
                $sejours[$_object->_id] = $_object;
                break;
            case "CAffectation":
                if ($_object->sejour_id) {
                    $sejours[$_object->sejour_id] = $_object->_ref_sejour;
                }
        }
    }

    CSejour::massLoadRefFirstLiaisonForPrestation($sejours, $prestation_id);
    CSejour::massLoadLiaisonsForPrestation($sejours, $prestation_id, $date_min, $date_max);
}

function loadAffectationsPermissions($service, $date, $mode, $prestation_id = null, &$affectations = null)
{
    $service_perm          = new CService();
    $service_perm->externe = 1;

    $services_perm = $service_perm->loadMatchingList();

    if (!$affectations) {
        $affectations = [];
    }

    // Si le service à afficher fait partie des services externes, pas de traitement à effectuer
    if (array_key_exists($service->_id, $services_perm)) {
        return $affectations;
    }

    /** @var CService $_service */
    foreach ($service_perm->loadMatchingList() as $_service_perm) {
        foreach ($_service_perm->loadRefsAffectations($date, $mode) as $_affectation_perm) {
            if (!$_affectation_perm->sejour_id) {
                continue;
            }

            $_affectation_perm->loadRefsAffectations();
            $_affectation_perm->loadRefFunction();

            if ($_affectation_perm->_ref_next->_id) {
                continue;
            }

            $aff = new CAffectation();

            $where = [
                "sejour_id"  => "= '$_affectation_perm->sejour_id'",
                "entree"     => "<= '$_affectation_perm->entree'",
                "service_id" => "= '$service->_id'",
            ];

            if ($aff->loadObject($where, "entree DESC")) {
                $affectations[$aff->_id]     = $aff;
                $aff->_in_permission         = true;
                $aff->_in_permission_sup_48h = CMbDT::hoursRelative($_affectation_perm->entree, CMbDT::dateTime()) > 48;
                $aff->_affectation_perm_id   = $_affectation_perm->_id;
                $lit                         = $aff->loadRefLit();
                loadAffectation($aff, $date, $prestation_id);
                $aff->loadRefsAffectations();
                $aff->loadRefFunction();
                $aff->_ref_next->loadRefsAffectations();
                $aff->sortie = $aff->_ref_next->_ref_next->_id ? $aff->_ref_next->_ref_next->entree : $aff->_ref_sejour->sortie;
                foreach ($service->_ref_chambres[$lit->chambre_id]->_ref_lits[$lit->_id]->_ref_affectations as $_key => $_aff) {
                    if ($_aff->_id === $aff->_id) {
                        unset($service->_ref_chambres[$lit->chambre_id]->_ref_lits[$lit->_id]->_ref_affectations[$_key]);
                    }
                }
                $service->_ref_chambres[$lit->chambre_id]->_ref_lits[$lit->_id]->_ref_affectations[$aff->_id] = $aff;
            }
        }
    }

    return $affectations;
}

function loadAffectation($affectation, $date = null, $prestation_id = null)
{
    $affectation->loadRefPraticien()->loadRefFunction();
    $affectation->loadRefsAffectations();
    $affectation->loadRefFunction();
    $affectation->loadRefSejour()->loadRefPatient();
    $affectation->_ref_prev->loadRefLit();
    $affectation->_ref_next->loadRefLit();

    $sejour =& $affectation->_ref_sejour;
    $sejour->loadRefPrestation();
    $sejour->loadRefsOperations();
    $sejour->loadRefPraticien();
    $sejour->loadRefPatient();
    $sejour->_ref_patient->loadRefDossierMedical(false);
    $sejour->_ref_patient->updateBMRBHReStatus($sejour);
    $sejour->_ref_patient->loadRefsPatientHandicaps();

    if (CAppUI::gconf("dPhospi prestations systeme_prestations") == "expert" && $prestation_id) {
        $sejour->loadLiaisonsForDay($prestation_id, $date);
    }

    // Chargement des droits C2S
    $sejour->getDroitsC2S();

    foreach ($sejour->_ref_operations as $operation_id => $curr_operation) {
        $sejour->_ref_operations[$operation_id]->loadExtCodesCCAM();
    }
}
