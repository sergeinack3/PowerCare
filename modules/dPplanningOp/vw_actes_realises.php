<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
$_date_min  = CView::get("_date_min", "date", true);
$_date_max  = CView::get("_date_max", "date", true);
$_prat_id   = CView::get("chir", "str", true);
$typeVue    = CView::get("typeVue", "str", true);
$bloc_id    = CView::get("bloc_id", "ref class|CBlocOperatoire");
$order      = CView::get('order', "str default|sortie_reelle");
$export_csv = CView::get("export_csv", "bool default|0");
CView::checkin();
CView::enableSlave();

$nbActes           = [];
$montantSejour     = [];
$tabSejours        = [];
$dates_actes       = [];
$totalActes        = 0;
$montantTotalActes = [
    'total' => 0,
    'dh'    => 0,
    'base'  => 0,
];

$praticien = new CMediusers();
$praticien->load($_prat_id);
$wherePrat = $praticien->getUserSQLClause();
$prat_ids  = [$_prat_id];
foreach ($praticien->_ref_secondary_users as $_user) {
    $prat_ids[] = $_user->_id;
}

$date_min        = "$_date_min 00:00:00";
$date_max        = "$_date_max 23:59:59";
$date_min_filter = $date_max_filter = null;
if ($order === "acte_execution") {
    $date_min_filter = $_date_min;
    $date_max_filter = $_date_max;
}

//Actes de consultation de séjour
$ljoin                 = [];
$ljoin["consultation"] = "consultation.sejour_id = sejour.sejour_id";
$ljoin["acte_ccam"]    = "consultation.consultation_id = acte_ccam.object_id AND acte_ccam.object_class = 'CConsultation'";
$where                 = [];
$where[]               = "acte_ccam.facturable = '1'";

$where_order = ($order === "sortie_reelle") ? "sejour.sortie" : "acte_ccam.execution";
$where[]     = "$where_order BETWEEN '$date_min' AND '$date_max'";

$where[] = "acte_ccam.executant_id $wherePrat";
$sejour  = new CSejour();
$sejours = $bloc_id ? [] : $sejour->loadList($where, null, null, "sejour_id", $ljoin);

$ljoin                 = [];
$ljoin["consultation"] = "consultation.sejour_id = sejour.sejour_id";
$ljoin["acte_ngap"]    = "consultation.consultation_id = acte_ngap.object_id AND acte_ngap.object_class = 'CConsultation'";
$where2                = [];
$where2[]              = "acte_ngap.facturable = '1'";
$where_order           = ($order === "sortie_reelle") ? "sejour.sortie" : "acte_ngap.execution";
$where2[]              = "$where_order BETWEEN '$date_min' AND '$date_max'";
$where2[]              = "acte_ngap.executant_id $wherePrat";
$sejour                = new CSejour();
$sejours_ngap          = $bloc_id ? [] : $sejour->loadList($where2, null, null, "sejour_id", $ljoin);
foreach ($sejours_ngap as $_sejour_ngap) {
    if (!isset($sejours[$_sejour_ngap->_id])) {
        $sejours[$_sejour_ngap->_id] = $_sejour_ngap;
    }
}

//Actes de d'interventions
$ljoin                       = [];
$ljoin["operations"]         = "operations.sejour_id = sejour.sejour_id";
$ljoin["acte_ccam"]          = "operations.operation_id = acte_ccam.object_id AND acte_ccam.object_class = 'COperation'";
$where["operations.annulee"] = " = '0'";
if ($bloc_id) {
    $ljoin["sallesbloc"]                         = "sallesbloc.salle_id = operations.salle_id";
    $ljoin["bloc_operatoire"]                    = "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id";
    $where["operations.salle_id"]                = " IS NOT NULL";
    $where["bloc_operatoire.bloc_operatoire_id"] = " = '$bloc_id'";
}
$sejours_consult = $sejour->loadList($where, null, null, "sejour_id", $ljoin);
foreach ($sejours_consult as $_sejour_consult) {
    if (!isset($sejours[$_sejour_consult->_id])) {
        $sejours[$_sejour_consult->_id] = $_sejour_consult;
    }
}

$ljoin                        = [];
$ljoin["operations"]          = "operations.sejour_id = sejour.sejour_id";
$ljoin["acte_ngap"]           = "operations.operation_id = acte_ngap.object_id AND acte_ngap.object_class = 'COperation'";
$where2["operations.annulee"] = " = '0'";
if ($bloc_id) {
    $ljoin["sallesbloc"]                          = "sallesbloc.salle_id = operations.salle_id";
    $ljoin["bloc_operatoire"]                     = "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id";
    $where2["operations.salle_id"]                = " IS NOT NULL";
    $where2["bloc_operatoire.bloc_operatoire_id"] = " = '$bloc_id'";
}
$sejours_consult_ngap = $sejour->loadList($where2, null, null, "sejour_id", $ljoin);
foreach ($sejours_consult_ngap as $_sejour_consult_ngap) {
    if (!isset($sejours[$_sejour_consult_ngap->_id])) {
        $sejours[$_sejour_consult_ngap->_id] = $_sejour_consult_ngap;
    }
}

//Actes de séjour
$ljoin              = [];
$ljoin["acte_ccam"] = "sejour.sejour_id = acte_ccam.object_id AND acte_ccam.object_class = 'CSejour'";
unset($where["operations.annulee"]);
$sejours_other = $bloc_id ? [] : $sejour->loadList($where, null, null, "sejour_id", $ljoin);
foreach ($sejours_other as $_sejour_other) {
    if (!isset($sejours[$_sejour_other->_id])) {
        $sejours[$_sejour_other->_id] = $_sejour_other;
    }
}

$ljoin              = [];
$ljoin["acte_ngap"] = "sejour.sejour_id = acte_ngap.object_id AND acte_ngap.object_class = 'CSejour'";
unset($where2["operations.annulee"]);
$sejours_other_ngap = $bloc_id ? [] : $sejour->loadList($where2, null, null, "sejour_id", $ljoin);
foreach ($sejours_other_ngap as $_sejour_other_ngap) {
    if (!isset($sejours[$_sejour_other_ngap->_id])) {
        $sejours[$_sejour_other_ngap->_id] = $_sejour_other_ngap;
    }
}

foreach ($sejours as $sejour) {
    /* @var CSejour $sejour */
    $sejour->loadRefPatient();
    $sejour->loadRefsOperations();
    $sejour->loadRefsConsultations();
    $sejour->loadRefsActes(null, 1, $date_min_filter, $date_max_filter);
    $sejour->_bill_prat_id = $_prat_id;
    $sejour->loadRefFacture();
    foreach ($sejour->_ref_operations as $op) {
        $op->loadRefsActes(null, 1, $date_min_filter, $date_max_filter);
        if (!count($op->_ref_actes)) {
            unset($sejour->_ref_operations[$op->_id]);
        }
    }
    foreach ($sejour->_ref_consultations as $consult) {
        $consult->loadRefsActes(null, 1, $date_min_filter, $date_max_filter);
        if (!count($consult->_ref_actes)) {
            unset($sejour->_ref_consultations[$consult->_id]);
        }
        if ($sejour->_ref_facture && !$sejour->_ref_facture->_id && $consult->sejour_id) {
            $consult->loadRefFacture();
            if ($consult->_ref_facture->_id) {
                $consult->loadRefFacture()->loadRefsReglements();
                $sejour->_ref_facture = $consult->_ref_facture;
            }
        }
    }
    if (!count($sejour->_ref_actes) && !count($sejour->_ref_operations) && !count($sejour->_ref_consultations)) {
        unset($sejours[$sejour->_id]);
    } else {
        if (CModule::getActive("dPfacturation") && CAppUI::gconf("dPplanningOp CFactureEtablissement use_facture_etab") && CAppUI::conf(
                "ref_pays"
            ) == 1) {
            if (!$sejour->_ref_facture->_id) {
                if ($msg = CFacture::save($sejour)) {
                    CApp::log("Log from vw_actes_realises", $msg);
                }
                $sejour->loadRefFacture();
            }

            $facture               = $sejour->_ref_facture;
            $facture->_ref_sejours = [$sejour->_id => $sejour];
            $facture->_bill_prat_id = $_prat_id;
            $facture->loadRefsObjects();
            if ($msg = $facture->store()) {
                CApp::log("Log from vw_actes_realises", $msg);
            }
            // Ajout de reglements
            $use_mode_default                           = CAppUI::gconf("dPfacturation CReglement use_mode_default");
            $facture->_new_reglement_patient["montant"] = $facture->_du_restant;
            $facture->_new_reglement_patient["mode"]    = $use_mode_default != "none" ? $use_mode_default : "autre";
        }
        $sejour->loadRefPatient();
        $nbActes[$sejour->_id]       = 0;
        $montantSejour[$sejour->_id] = 0;
        // Calcul du nombre d'actes par sejour
        if ($sejour->_ref_actes) {
            if (count($sejour->_ref_actes)) {
                foreach ($sejour->_ref_actes as $key_acte => $acte) {
                    if (in_array($acte->executant_id, $prat_ids)) {
                        $nbActes[$sejour->_id]++;
                        $montantSejour[$sejour->_id] += $acte->_montant_facture;
                        $montantTotalActes['base']   += $acte->montant_base;
                        $montantTotalActes['dh']     += $acte->montant_depassement;

                        if ($order == 'acte_execution') {
                            $date = CMbDT::date($acte->execution);
                            if (!array_key_exists($date, $tabSejours) || !array_key_exists($sejour->_id, $tabSejours[$date])) {
                                $tabSejours[$date][$sejour->_id] = $sejour;
                                $dates_actes[$date][]            = $sejour->_guid;
                            }
                        }
                    } else {
                        unset($sejour->_ref_actes[$key_acte]);
                    }
                }
            }
        }
        if ($sejour->_ref_operations) {
            foreach ($sejour->_ref_operations as $operation) {
                if (count($operation->_ref_actes)) {
                    $operation->loadRefPlageOp();
                    foreach ($operation->_ref_actes as $key_acte => $acte) {
                        if (in_array($acte->executant_id, $prat_ids)) {
                            $nbActes[$sejour->_id]++;
                            $montantSejour[$sejour->_id] += $acte->_montant_facture;
                            $montantTotalActes['base']   += $acte->montant_base;
                            $montantTotalActes['dh']     += $acte->montant_depassement;

                            if ($order == 'acte_execution') {
                                $date = CMbDT::date($acte->execution);
                                if (!array_key_exists($date, $tabSejours) || !array_key_exists($sejour->_id, $tabSejours[$date])) {
                                    $tabSejours[$date][$sejour->_id] = $sejour;
                                    $dates_actes[$date][]            = $operation->_guid;
                                }
                            }
                        } else {
                            unset($operation->_ref_actes[$key_acte]);
                        }
                    }
                }
            }
        }
        if ($sejour->_ref_consultations) {
            foreach ($sejour->_ref_consultations as $consult) {
                if (count($consult->_ref_actes)) {
                    foreach ($consult->_ref_actes as $key_acte => $acte) {
                        if (in_array($acte->executant_id, $prat_ids)) {
                            $nbActes[$sejour->_id]++;
                            $montantSejour[$sejour->_id] += $acte->_montant_facture;
                            $montantTotalActes['base']   += $acte->montant_base;
                            $montantTotalActes['dh']     += $acte->montant_depassement;

                            if ($order == 'acte_execution') {
                                $date = CMbDT::date($acte->execution);
                                if (!array_key_exists($date, $tabSejours) || !array_key_exists($sejour->_id, $tabSejours[$date])) {
                                    $tabSejours[$date][$sejour->_id] = $sejour;
                                    $dates_actes[$date][]            = $consult->_guid;
                                }
                            }
                        } else {
                            unset($consult->_ref_actes[$key_acte]);
                        }
                    }
                }
            }
        }

        if ($order == 'sortie_reelle') {
            $tabSejours[CMbDT::date($sejour->sortie)][$sejour->_id] = $sejour;
        }

        $totalActes                 += $nbActes[$sejour->_id];
        $montantTotalActes['total'] += $montantSejour[$sejour->_id];
    }
}

// Tri par date du tableau de sejours
ksort($tabSejours);

$bloc = new CBlocOperatoire();
$bloc->load($bloc_id);

if (!$export_csv) {
    // Création du template
    $smarty = new CSmartyDP();
    $smarty->assign("montantTotalActes", $montantTotalActes);
    $smarty->assign("totalActes", $totalActes);
    $smarty->assign("nbActes", $nbActes);
    $smarty->assign("sejours", $tabSejours);
    $smarty->assign("montantSejour", $montantSejour);
    $smarty->assign("praticien", $praticien);
    $smarty->assign('prat_ids', $prat_ids);
    $smarty->assign("_date_min", $_date_min);
    $smarty->assign("_date_max", $_date_max);
    $smarty->assign("typeVue", $typeVue);
    $smarty->assign("bloc", $bloc);
    $smarty->assign('order', $order);
    $smarty->assign('dates_actes', $dates_actes);
    $smarty->display("vw_actes_realises");
} else {
    $use_facture_etab = CModule::getActive("dPfacturation") && CAppUI::gconf("dPplanningOp CFactureEtablissement use_facture_etab");
    $file             = new CCSVFile();
    //Titres
    $titles = [
        CAppUI::tr($order == 'sortie_reelle' ? "CSejour-sortie_reelle" : "Date"),
        CAppUI::tr("CFactureEtablissement-patient_id"),
        "Total Séjour",
        CAppUI::tr("CSejour-type"),
        CAppUI::tr("CActeCCAM-object_class"),
        CAppUI::tr("CActeCCAM-code_acte"),
        CAppUI::tr("CCodageCCAM-activite_anesth-court"),
        CAppUI::tr("CActeCCAM-code_phase"),
        'Mod',
        'ANP',
        CAppUI::tr("CActeCCAM-montant_base"),
        CAppUI::tr("CActeCCAM-montant_depassement"),
        CAppUI::tr("CActeCCAM-_montant_facture"),
    ];
    if ($use_facture_etab) {
        $titles[] = "Dû établissement";
    }
    $file->writeLine($titles);
    foreach ($tabSejours as $date => $jour) {
        $date_line = CMbDT::format($date, CAppUI::conf("date"));
        foreach ($jour as $sejour) {
            $line_sejour = [
                $date_line,
                $sejour->_ref_patient->_view . ($sejour->_ref_patient->_age ? " (" . $sejour->_ref_patient->_age . ")" : ""),
                $montantSejour[$sejour->_id],
                CAppUI::tr("CSejour._type_admission.$sejour->type"),
            ];

            $list_actes_sejour = [];
            if (count($sejour->_ref_actes) &&
                ($order == 'sortie_reelle' || ($order == 'acte_execution' && in_array($sejour->_guid, $dates_actes[$date])))) {
                $list_actes_sejour[$sejour->_guid] = $sejour;
            }
            if ($sejour->_ref_operations) {
                foreach ($sejour->_ref_operations as $operation) {
                    if (count($operation->_ref_actes) && ($order == 'sortie_reelle' ||
                            ($order == 'acte_execution' && in_array($operation->_guid, $dates_actes[$date])))) {
                        $list_actes_sejour[$operation->_guid] = $operation;
                    }
                }
            }
            if ($sejour->_ref_consultations) {
                foreach ($sejour->_ref_consultations as $consult) {
                    if (count($consult->_ref_actes) && ($order == 'sortie_reelle' ||
                            ($order == 'acte_execution' && in_array($consult->_guid, $dates_actes[$date])))) {
                        $list_actes_sejour[$consult->_guid] = $consult;
                    }
                }
            }
            foreach ($list_actes_sejour as $object) {
                foreach ($object->_ref_actes as $acte) {
                    if ($acte->executant_id == $praticien->_id &&
                        (($order == 'acte_execution' && CMbDT::date($acte->execution) == $date) || $order == 'sortie_reelle')) {
                        $line_acte_sejour = $line_sejour;
                        $libelle_object   = "";
                        if ($object instanceof CSejour) {
                            $libelle_object .= CAppUI::tr($object->_class) . " " . CAppUI::tr("date.from") . " ";
                            $libelle_object .= CMbDT::format($object->entree, CAppUI::conf("datetime")) . " " . CAppUI::tr("date.to") . " ";
                            $libelle_object .= CMbDT::format($object->sortie, CAppUI::conf("datetime"));
                        } elseif ($object instanceof COperation) {
                            $libelle_object .= CAppUI::tr("COperation-Intervention of %s", CMbDT::format($object->_datetime_best, '%d %B %Y'));
                            if ($object->libelle) {
                                $libelle_object .= ": $object->libelle";
                            }
                        } else {
                            $libelle_object .= CAppUI::tr("dPcabinet-Consultation of %s", CMbDT::format($object->_datetime, '%d %B %Y'));
                            if ($object->motif) {
                                $libelle_object .= ": $object->motif";
                            }
                        }
                        $line_acte_sejour[] = $libelle_object;
                        $line_acte_sejour[] = $acte->_class == "CActeCCAM" ? $acte->code_acte : $acte->code;
                        $line_acte_sejour[] = $acte->_class == "CActeCCAM" ? $acte->code_activite : "";
                        $line_acte_sejour[] = $acte->_class == "CActeCCAM" ? $acte->code_phase : "";
                        $line_acte_sejour[] = $acte->_class == "CActeCCAM" ? $acte->modificateurs : "";
                        $line_acte_sejour[] = $acte->_class == "CActeCCAM" ? $acte->code_association : "";
                        $line_acte_sejour[] = $acte->montant_base;
                        $line_acte_sejour[] = $acte->montant_depassement;
                        $line_acte_sejour[] = $acte->_montant_facture;
                        if ($use_facture_etab) {
                            $line_acte_sejour[] = $sejour->_ref_facture->_id ? $sejour->_ref_facture->_new_reglement_patient["montant"] : "";
                        }
                        $file->writeLine($line_acte_sejour);
                    }
                }
            }
        }
    }

    $file_name = CAppUI::tr("Compta.valid_money") . "_";
    if ($_prat_id && $praticien->_id) {
        $file_name .= $praticien->_user_first_name . "_" . $praticien->_user_last_name . "_";
    }
    if ($_date_min != $_date_max) {
        $file_name .= CMbDT::format($_date_min, '%d-%m-%Y') . "_" . CAppUI::tr("date.to") . "_" . CMbDT::format($_date_max, '%d-%m-%Y');
    } else {
        $file_name .= CMbDT::format($_date_min, '%d-%m-%Y');
    }

    $file->stream($file_name);
    CApp::rip();
}
