<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $g;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Cabinet\CTarif;

CCanDo::checkEdit();
// Récupération des paramètres
$filter                          = new CConsultation();
$filter->_date_min               = CView::get("_date_min", "date default|now", true);
$filter->_date_max               = CView::get("_date_max", "date default|now", true);
$filter->_etat_reglement_patient = CView::get("_etat_reglement_patient", "str", true);
$filter->_etat_reglement_tiers   = CView::get("_etat_reglement_tiers", "str", true);
$filter->_etat_accident_travail  = CView::get("_etat_accident_travail", "str", true);
$filter->_mode_reglement         = CView::get("mode", "str default|0", true);
$filter->_type_affichage         = CView::get("_type_affichage", "str default|1", true);
$all_group_money                 = CView::get("all_group_money", "bool default|1", true);
$all_impayes                     = CView::get("all_impayes", "bool default|0");
$plage_id                        = CView::getRefCheckRead("plage_id", "ref class|CPlageconsult");
$cs                              = CView::get("cs", "bool", true);
$chir_id                         = CView::getRefCheckRead("chir", "str", true);
$tarif_id                        = CView::get("tarif", "str", true);
$category_id                     = CView::get("category_id", "ref class|CFactureCategory", true);
$export_csv                      = CView::get("export_csv", "bool default|0");
$lieu_id                         = CView::get("lieu", "ref class|CLieuConsult");

CView::checkin();
CView::enableSlave();

// Traduction pour le passage d'un enum en bool pour les requetes sur la base de donnee
if ($filter->_type_affichage == "complete") {
    $filter->_type_affichage = 1;
} elseif ($filter->_type_affichage == "totaux") {
    $filter->_type_affichage = 0;
}

$where = [];
$ljoin = [];

// Plage ciblée
if ($plage_id) {
    // Contraintes sur les plages de consultation
    $ljoin["facture_liaison"] = "facture_liaison.facture_id = facture_cabinet.facture_id";
    $ljoin["consultation"]    = "facture_liaison.object_id = consultation.consultation_id";
    $ljoin["plageconsult"]    = "consultation.plageconsult_id = plageconsult.plageconsult_id";

    $where["facture_liaison.facture_class"] = " = 'CFactureCabinet'";
    $where["facture_liaison.object_class"]  = " = 'CConsultation'";
    $where["plageconsult.plageconsult_id"]  = " = '$plage_id'";
} // Tri sur les dates
else {
    $where["ouverture"] = "BETWEEN '$filter->_date_min 00:00:00' AND '$filter->_date_max 23:59:59'";
}
if ($category_id) {
    $where["category_id"] = " = '$category_id'";
}
if ($filter->_etat_accident_travail) {
    $ljoin["facture_liaison"] = "facture_liaison.facture_id = facture_cabinet.facture_id";
    $ljoin["consultation"]    = "facture_liaison.object_id = consultation.consultation_id";

    $where["facture_liaison.facture_class"] = " = 'CFactureCabinet'";
    $where["facture_liaison.object_class"]  = " = 'CConsultation'";
    if ($filter->_etat_accident_travail == "yes") {
        $where["consultation.date_at"] = "IS NOT NULL";
    } elseif ($filter->_etat_accident_travail == "no") {
        $where["consultation.date_at"] = "IS NULL";
    }
}
// Consultations gratuites
if (!$cs) {
    $where[] = "facture_cabinet.du_patient + facture_cabinet.du_tiers > 0";
}

// Filtre sur les praticiens
$listPrat = CConsultation::loadPraticiensCompta($chir_id);

$where["praticien_id"] = CSQLDataSource::prepareIn(array_keys($listPrat));

// Initialisation du tableau de reglements
$reglement               = new CReglement();
$recapReglement["total"] = [
    "nb_consultations"     => "0",
    "reste_patient"        => "0",
    "reste_tiers"          => "0",
    "du_patient"           => "0",
    "du_tiers"             => "0",
    "nb_reglement_patient" => "0",
    "nb_reglement_tiers"   => "0",
    "nb_impayes_tiers"     => "0",
    "nb_impayes_patient"   => "0",
    "secteur1"             => "0",
    "secteur2"             => "0",
    "secteur3"             => "0",
    "du_tva"               => "0",
];
if ($filter->_etat_accident_travail != "no") {
    $recapReglement["total"]["nb_accidents"] = 0;
}
foreach (array_merge($reglement->_specs["mode"]->_list, [""]) as $_mode) {
    $recapReglement[$_mode] = [
        "du_patient"           => "0",
        "du_tiers"             => "0",
        "nb_reglement_patient" => "0",
        "nb_reglement_tiers"   => "0",
    ];
}

// Etat des règlements
if ($all_impayes) {
    $where[] = "(facture_cabinet.patient_date_reglement IS NULL AND facture_cabinet.du_patient > 0)
    || (facture_cabinet.tiers_date_reglement IS NULL AND facture_cabinet.du_tiers > 0)";
} else {
    if ($filter->_etat_reglement_patient == "reglee") {
        $where["patient_date_reglement"] = "IS NOT NULL";
    }
    if ($filter->_etat_reglement_patient == "non_reglee") {
        $where["patient_date_reglement"]     = "IS NULL";
        $where["facture_cabinet.du_patient"] = "> 0";
    }

    if ($filter->_etat_reglement_tiers == "reglee") {
        $where["tiers_date_reglement"] = "IS NOT NULL";
    }
    if ($filter->_etat_reglement_tiers == "non_reglee") {
        $where["tiers_date_reglement"]     = "IS NULL";
        $where["facture_cabinet.du_tiers"] = "> 0";
    }
}

// Tarifs
if ($tarif_id) {
    $tarif                       = CTarif::findOrFail($tarif_id);
    $where["consultation.tarif"] = " = '$tarif->description'";
}

$ljoin["facture_liaison"] = "facture_liaison.facture_id = facture_cabinet.facture_id";
$ljoin["consultation"]    = "facture_liaison.object_id = consultation.consultation_id";
// Reglements via les factures de consultation
$where["cloture"]                    = "IS NOT NULL";
$where["consultation.annule"]        = "= '0'";
$where["facture_cabinet.patient_id"] = "IS NOT NULL";
$where["facture_cabinet.annule"]     = "= '0'";
$order                               = "ouverture, praticien_id";

$facture = new CFactureCabinet();
if ($all_group_money) {
    $listFactures = $facture->loadList($where, $order, null, "facture_id", $ljoin);
} else {
    $listFactures = $facture->loadGroupList($where, $order, null, "facture_id", $ljoin);
}

CFactureCabinet::massCheckNumCompta($listFactures, $facture);

$listPlages = [];

CStoredObject::massLoadFwdRef($listFactures, "group_id");
$patients = CStoredObject::massLoadFwdRef($listFactures, "patient_id");
CStoredObject::massLoadBackRefs($patients, "correspondants_patient");
CStoredObject::massLoadFwdRef($listFactures, "praticien_id");
CStoredObject::massLoadBackRefs($listFactures, "reglements");
CStoredObject::massLoadBackRefs($listFactures, "notes");
CStoredObject::massLoadBackRefs($listFactures, "items");
CStoredObject::massLoadBackRefs($listFactures, "relance_fact", "date");

$use_cotation_ccam = CAppUI::gconf("dPccam codage use_cotation_ccam");
foreach ($listFactures as $_facture) {
    /* @var CFactureCabinet $_facture */
    if ($g != $_facture->group_id) {
        $_facture->loadRefGroup();
    }
    $_facture->loadRefPatient();
    $_facture->loadRefPraticien();
    $_facture->loadRefsConsultation();

    if (count($_facture->_ref_consults)) {
        $_facture->loadRefCoeffFacture();
        $_facture->updateMontants();
        $_facture->loadRefsReglements();
        $_facture->loadRefsNotes();

        $plage = $_facture->_ref_last_consult->_ref_plageconsult;
        $plage->loadRefAgendaPraticien();

        if ($lieu_id && $plage->_ref_agenda_praticien->lieuconsult_id != $lieu_id) {
            continue;
        }
        if ($all_impayes) {
            $_facture->loadRefsRelances();
        }
        // Ajout de reglements
        $_facture->_new_reglement_patient["montant"] = $_facture->_du_restant_patient;
        $_facture->_new_reglement_tiers["montant"]   = $_facture->_du_restant_tiers;

        $recapReglement["total"]["nb_consultations"] += count($_facture->_ref_consults);

        $recapReglement["total"]["du_patient"]    += $_facture->_reglements_total_patient;
        $recapReglement["total"]["du_patient"]    += $_facture->_montant_avoir;
        $recapReglement["total"]["du_tva"]        += $_facture->du_tva;
        $recapReglement["total"]["reste_patient"] += $_facture->_du_restant_patient;
        if ($_facture->_du_restant_patient && !$_facture->patient_date_reglement && $_facture->du_patient) {
            $recapReglement["total"]["nb_impayes_patient"]++;
        }

        $recapReglement["total"]["du_tiers"]    += $_facture->_reglements_total_tiers;
        $recapReglement["total"]["reste_tiers"] += $_facture->_du_restant_tiers;
        if ($_facture->_du_restant_tiers) {
            $recapReglement["total"]["nb_impayes_tiers"]++;
        }

        $recapReglement["total"]["nb_reglement_patient"] += count($_facture->_ref_reglements_patient);
        $recapReglement["total"]["nb_reglement_tiers"]   += count($_facture->_ref_reglements_tiers);
        if ($use_cotation_ccam) {
            $recapReglement["total"]["secteur1"] += $_facture->_secteur1;
            $recapReglement["total"]["secteur2"] += $_facture->_secteur2;
            $recapReglement["total"]["secteur3"] += $_facture->_secteur3;
        } else {
            $recapReglement["total"]["secteur1"] += $_facture->_montant_avec_remise;
        }

        foreach ($_facture->_ref_reglements_patient as $_reglement) {
            $recapReglement[$_reglement->mode]["du_patient"] += $_reglement->montant;
            $recapReglement[$_reglement->mode]["nb_reglement_patient"]++;
        }

        foreach ($_facture->_ref_reglements_tiers as $_reglement) {
            $recapReglement[$_reglement->mode]["du_tiers"] += $_reglement->montant;
            $recapReglement[$_reglement->mode]["nb_reglement_tiers"]++;
        }

        // Classement par plage
        $plage->loadRefsFwd();
        if ($_facture->_ref_last_consult->_id) {
            $debut_plage = "$plage->date $plage->debut";
            if (!isset($listPlages["$debut_plage"])) {
                $listPlages["$debut_plage"]["plage"]             = $plage;
                $listPlages["$debut_plage"]["total"]["secteur1"] = 0;
                $listPlages["$debut_plage"]["total"]["secteur2"] = 0;
                $listPlages["$debut_plage"]["total"]["secteur3"] = 0;
                $listPlages["$debut_plage"]["total"]["du_tva"]   = 0;
                $listPlages["$debut_plage"]["total"]["total"]    = 0;
                $listPlages["$debut_plage"]["total"]["patient"]  = 0;
                $listPlages["$debut_plage"]["total"]["tiers"]    = 0;
            }

            $listPlages["$debut_plage"]["factures"][$_facture->_guid] = $_facture;
            $listPlages["$debut_plage"]["total"]["secteur1"]          += $_facture->_secteur1;

            if ($use_cotation_ccam) {
                $listPlages["$debut_plage"]["total"]["secteur2"] += $_facture->_secteur2;
            } else {
                $listPlages["$debut_plage"]["total"]["secteur2"] += $_facture->remise;
            }
            $listPlages["$debut_plage"]["total"]["secteur3"] += $_facture->_secteur3;
            $listPlages["$debut_plage"]["total"]["du_tva"]   += $_facture->du_tva;
            $listPlages["$debut_plage"]["total"]["total"]    += $_facture->_montant_avec_remise;
            $listPlages["$debut_plage"]["total"]["patient"]  += $_facture->_reglements_total_patient;
            $listPlages["$debut_plage"]["total"]["tiers"]    += $_facture->_reglements_total_tiers;

            if ($filter->_etat_accident_travail != "no" && $_facture->_ref_last_consult->date_at) {
                $recapReglement["total"]["nb_accidents"]++;
            }
        }
    }
}

$type_aff = 1;
if (!$export_csv) {
    // Création du template
    $smarty = new CSmartyDP();
    $smarty->assign("today", CMbDT::date());
    $smarty->assign("filter", $filter);
    $smarty->assign("listPrat", $listPrat);
    $smarty->assign("listPlages", $listPlages);
    $smarty->assign("recapReglement", $recapReglement);
    $smarty->assign("reglement", $reglement);
    $smarty->assign("all_impayes", $all_impayes);
    $smarty->assign("type_aff", $type_aff);
    $smarty->display("print_rapport.tpl");
} else {
    $file = new CCSVFile();
    //Titres
    $titles = [
        CAppUI::tr("CFactureCabinet"),
        CAppUI::tr("CConsultation-patient_id"),
        CAppUI::tr('birthDate'),
        CAppUI::tr("CFactureCabinet-praticien_id"),
        CAppUI::tr("CConsultation-_date"),
    ];
    if ($filter->_etat_accident_travail != 'no') {
        $titles[] = CAppUI::tr("CConsultation-AT-desc");
    }
    $titles[] = CAppUI::tr("CConsultation-tarif");
    if ($type_aff) {
        $titles2 = [
            CAppUI::tr("CConsultation-secteur1"),
            CAppUI::tr("CConsultation-secteur2"),
            CAppUI::tr("CConsultation-secteur3"),
            CAppUI::tr("CConsultation-du_tva"),
            CAppUI::tr("CConsultation-_somme"),
            CAppUI::tr("CConsultation-du_patient"),
            CAppUI::tr("CConsultation-du_tiers"),
        ];
    } else {
        $titles2 = [
            CAppUI::tr("CFacture-montant"),
            CAppUI::tr("CFactureCabinet-remise"),
            CAppUI::tr("CConsultation-_somme"),
            CAppUI::tr("CConsultation-du_patient"),
        ];
    }
    $titles   = array_merge_recursive($titles, $titles2);
    $titles[] = CAppUI::tr("CFactureCabinet-patient_date_reglement");
    $file->writeLine($titles);

    foreach ($listPlages as $_plage) {
        foreach ($_plage['factures'] as $_facture) {
            $line_rapport = [
                $_facture->_view . ($_facture->group_id != $g ? "$_facture->_ref_group->_view" : ""),
                $_facture->_ref_patient->nom . ' ' . $_facture->_ref_patient->prenom,
                CMbDT::format($_facture->_ref_patient->naissance, CAppUI::conf("date")),
                $_facture->_ref_praticien->_view,
                CMbDT::format($_plage['plage']->date, CAppUI::conf("date")),
            ];
            if ($filter->_etat_accident_travail != 'no') {
                $line_rapport[] = CMbDT::format($_facture->_ref_last_consult->date_at, CAppUI::conf("date"));
            }
            $line_tarif = "";
            foreach ($_facture->_ref_consults as $_consult) {
                if ($line_tarif) {
                    $line_tarif .= ' - ';
                }
                $line_tarif .= CMbDT::format($_consult->_date, CAppUI::conf("date")) . ": " . $_consult->tarif;
            }
            $line_rapport[] = $line_tarif;
            $line_rapport[] = $_facture->_secteur1;
            if ($type_aff) {
                $line_rapport[] = $_facture->_secteur2;
                $line_rapport[] = $_facture->_secteur3;
                $line_rapport[] = $_facture->du_tva;
            } else {
                $line_rapport[] = $_facture->remise;
            }
            $line_rapport[] = $_facture->_montant_avec_remise;
            $line_rapport[] = abs($_facture->_du_restant_patient) > 0.01 ? $_facture->_new_reglement_patient["montant"] : "";
            if ($type_aff) {
                $line_rapport[] = abs($_facture->_du_restant_tiers) > 0.01 ? $_facture->_new_reglement_tiers["montant"] : "";
            }
            $line_rapport[] = $_facture->patient_date_reglement ? CMbDT::format($_facture->patient_date_reglement, CAppUI::conf("date")) : "";
            $file->writeLine($line_rapport);
        }
    }
    $file_name = CAppUI::tr("Compta.valid_money-filename") . "_";
    if ($chir_id && $listPrat[$chir_id]) {
        $file_name .= $listPrat[$chir_id]->_user_first_name . "_" . $listPrat[$chir_id]->_user_last_name . "_";
    }
    if ($filter->_date_min != $filter->_date_max) {
        $file_name .= CMbDT::format($filter->_date_min, '%d-%m-%Y') . "_" . CAppUI::tr("date.to") . "_" . CMbDT::format(
                $filter->_date_max,
                '%d-%m-%Y'
            );
    } else {
        $file_name .= CMbDT::format($filter->_date_min, '%d-%m-%Y');
    }
    $file->stream($file_name);
    CApp::rip();
}
