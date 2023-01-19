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
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkEdit();
// Récupération des paramètres
$filter                  = new CPlageconsult();
$filter->_date_min       = CView::get("_date_min", "date default|now", true);
$filter->_date_max       = CView::get("_date_max", "date default|now", true);
$filter->_mode_reglement = CView::get("mode", "str default|0", true);
$filter->_type_affichage = CView::get("_type_affichage", "str default|1", true);
$all_group_compta        = CView::get("all_group_compta", "bool default|1", true);
$chir_id                 = CView::getRefCheckRead("chir", "str", true);
$type_view               = CView::get("type_view", "str default|consult");
$export_csv              = CView::get("export_csv", "bool default|0");
$tarif_id                = CView::get("tarif", "str", true);
$category_id             = CView::get("category_id", "ref class|CFactureCategory", true);
$lieu_id                 = CView::get("lieu", "ref class|CLieuConsult");

CView::checkin();
CView::enableSlave();

// Traduction pour le passage d'un enum en bool pour les requetes sur la base de donnee
if ($filter->_type_affichage == "complete") {
    $filter->_type_affichage = 1;
}
if ($filter->_type_affichage == "totaux") {
    $filter->_type_affichage = 0;
}

$ljoin = [];
$where = [];
// Filtre sur les dates
$where["reglement.date"] = "BETWEEN '$filter->_date_min 00:00:00' AND '$filter->_date_max 23:59:59'";

// Filtre sur les modes de paiement
if ($filter->_mode_reglement) {
    $where["reglement.mode"] = "= '$filter->_mode_reglement'";
}

// Filtre sur les praticiens
$listPrat      = CConsultation::loadPraticiensCompta($chir_id);
$view_etab     = $type_view == "etab";
$table_facture = $view_etab ? "facture_etablissement" : "facture_cabinet";
if ($category_id) {
    $where["$table_facture.category_id"] = " = '$category_id'";
}
// Chargement des règlements via les factures
$ljoin[$table_facture] = "reglement.object_id = $table_facture.facture_id";
if (!$all_group_compta) {
    $where["$table_facture.group_id"] = "= '" . CGroups::loadCurrent()->_id . "'";
}
$where["$table_facture.praticien_id"] = CSQLDataSource::prepareIn(array_keys($listPrat));
$where["reglement.object_class"]      = $view_etab ? " = 'CFactureEtablissement'" : " = 'CFactureCabinet'";

// Tarifs
if ($tarif_id) {
    $tarif = new CTarif();
    $tarif->load($tarif_id);
    $ljoin["facture_liaison"] = "facture_liaison.facture_id = $table_facture.facture_id";
    $ljoin["consultation"]    = "facture_liaison.object_id = consultation.consultation_id";

    $where["consultation.tarif"] = " = '$tarif->description'";
}

$reglement = new CReglement();
/** @var CReglement[] $reglements */
$reglements = $reglement->loadList($where, " $table_facture.facture_id, reglement.date", null, null, $ljoin);

$reglement = new CReglement();

$nb_object = $type_view == "consult" ? "nb_consultations" : "nb_evts";
$nb_object = $view_etab ? "nb_sejours" : $nb_object;
// Calcul du récapitulatif
// Initialisation du tableau de reglements
$recapReglement["total"] = [
    $nb_object             => "0",
    "du_patient"           => "0",
    "du_tiers"             => "0",
    "nb_reglement_patient" => "0",
    "nb_reglement_tiers"   => "0",
    "secteur1"             => "0",
    "secteur2"             => "0",
    "secteur3"             => "0",
    "du_tva"               => "0",
];

foreach (array_merge($reglement->_specs["mode"]->_list, [""]) as $_mode) {
    $recapReglement[$_mode] = [
        "du_patient"           => "0",
        "du_tiers"             => "0",
        "nb_reglement_patient" => "0",
        "nb_reglement_tiers"   => "0",
    ];
}

$listReglements = [];
$listObjects    = [];
$factures       = CStoredObject::massLoadFwdRef($reglements, "object_id");
$patients       = CStoredObject::massLoadFwdRef($factures, "patient_id");
CStoredObject::massLoadFwdRef($factures, "group_id");
CStoredObject::massCountBackRefs($factures, "notes");

$check_bill = [];
foreach ($reglements as $_reglement) {
    $facture = $_reglement->loadRefFacture();
    $facture->loadRefGroup();
    $facture->loadRefsNotes();
    $facture->loadRefsObjects();
    $facture->loadRefsReglements();
    $agenda = $_reglement->_ref_facture->_ref_last_consult->loadRefPlageConsult()->loadRefAgendaPraticien();
    if ($lieu_id && $lieu_id != $agenda->lieuconsult_id) {
        continue;
    }

    if ((count($facture->_ref_consults) && $type_view == "consult") ||
        (count($facture->_ref_evts) && $type_view == "evt") ||
        (count($facture->_ref_sejours) && $view_etab)) {
        if (CAppUI::gconf("dPccam codage use_cotation_ccam")) {
            if ($type_view == "consult") {
                foreach ($facture->_ref_consults as $_consult) {
                    if (!array_key_exists($_consult->_guid, $listObjects)) {
                        $listObjects[$_consult->_guid]       = true;
                        $recapReglement["total"]["secteur1"] += $_consult->secteur1;
                        $recapReglement["total"]["secteur2"] += $_consult->secteur2;
                        $recapReglement["total"]["secteur3"] += $_consult->secteur3;
                        $recapReglement["total"]["du_tva"]   += $_consult->du_tva;
                    }
                }
            } elseif ($type_view == "evt") {
                foreach ($facture->_ref_evts as $_evt) {
                    if (!array_key_exists($_evt->_guid, $listObjects)) {
                        $listObjects[$_evt->_guid] = true;
                        foreach ($_evt->loadRefsActes() as $_acte) {
                            $recapReglement["total"]["secteur1"] += $_acte->montant_base;
                            $recapReglement["total"]["secteur2"] += $_acte->montant_depassement;
                        }
                    }
                }
            } elseif ($view_etab) {
                foreach ($facture->_ref_sejours as $_sejour) {
                    if (!array_key_exists($_sejour->_guid, $listObjects)) {
                        $listObjects[$_sejour->_guid] = true;
                        foreach ($_sejour->loadRefsActes() as $_acte) {
                            $recapReglement["total"]["secteur1"] += $_acte->montant_base;
                            $recapReglement["total"]["secteur2"] += $_acte->montant_depassement;
                        }
                        foreach ($_sejour->loadRefsConsultations() as $_consult) {
                            foreach ($_consult->loadRefsActes() as $_acte) {
                                $recapReglement["total"]["secteur1"] += $_acte->montant_base;
                                $recapReglement["total"]["secteur2"] += $_acte->montant_depassement;
                            }
                        }
                        foreach ($_sejour->loadRefsOperations() as $_op) {
                            foreach ($_op->loadRefsActes() as $_acte) {
                                $recapReglement["total"]["secteur1"] += $_acte->montant_base;
                                $recapReglement["total"]["secteur2"] += $_acte->montant_depassement;
                            }
                        }
                    }
                }
            }
        } else {
            if (!isset($check_bill[$facture->_guid])) {
                $recapReglement["total"]["secteur1"] += $facture->_montant_avec_remise;
                $check_bill[$facture->_guid]         = 1;
            }
        }
        if ($type_view == "consult") {
            $recapReglement["total"][$nb_object] += count($facture->_ref_consults);
        } elseif ($type_view == "evt") {
            $recapReglement["total"][$nb_object] += count($facture->_ref_evts);
        } elseif ($view_etab) {
            $recapReglement["total"][$nb_object] += count($facture->_ref_sejours);
        }

        if ($_reglement->emetteur == "patient") {
            $recapReglement["total"]["du_patient"] += $_reglement->montant;
            $recapReglement["total"]["nb_reglement_patient"]++;
            $recapReglement[$_reglement->mode]["du_patient"] += $_reglement->montant;
            $recapReglement[$_reglement->mode]["nb_reglement_patient"]++;
        }

        if ($_reglement->emetteur == "tiers") {
            $recapReglement["total"]["du_tiers"] += $_reglement->montant;
            $recapReglement["total"]["nb_reglement_tiers"]++;
            $recapReglement[$_reglement->mode]["du_tiers"] += $_reglement->montant;
            $recapReglement[$_reglement->mode]["nb_reglement_tiers"]++;
        }

        // Totaux par date
        $date = CMbDT::date($_reglement->date);
        if (!isset($listReglements[$date])) {
            $listReglements[$date]["total"]["patient"] = 0;
            $listReglements[$date]["total"]["tiers"]   = 0;
            $listReglements[$date]["total"]["total"]   = 0;
        }

        $listReglements[$date]["total"][$_reglement->emetteur] += $_reglement->montant;
        $listReglements[$date]["total"]["total"]               += $_reglement->montant;
        $listReglements[$date]["reglements"][$_reglement->_id] = $_reglement;
    }
}

$type_object = $type_view == "consult" ? "CConsultation" : "CEvenementPatient";
$type_object = $view_etab ? "CSejour" : $type_object;
$type_aff    = 1;

if (!$export_csv) {
    // Création du template
    $smarty = new CSmartyDP();
    $smarty->assign("today", CMbDT::date());
    $smarty->assign("filter", $filter);
    $smarty->assign("listPrat", $listPrat);
    $smarty->assign("listReglements", $listReglements);
    $smarty->assign("recapReglement", $recapReglement);
    $smarty->assign("reglement", $reglement);
    $smarty->assign("type_view", $type_view);
    $smarty->assign("type_object", $type_object);
    $smarty->assign("nb_object", $nb_object);
    $smarty->assign("type_aff", $type_aff);
    $smarty->display("print_compta.tpl");
} else {
    $file = new CCSVFile();
    //Titres
    $titles   = [
        CAppUI::tr("CFactureCabinet"),
        CAppUI::tr("$type_object-" . ($type_view == "consult" ? "_prat_id" : "praticien_id")),
        CAppUI::tr($type_view == "evt" ? "CPatient" : "$type_object-patient_id"),
        $type_view != "etab" ? CAppUI::tr("$type_object-_date") . ": " . CAppUI::tr("$type_object-tarif") : CAppUI::tr("$type_object"),
    ];
    $titles[] = CAppUI::tr($type_aff ? "CFactureCabinet-_secteur1" : "CFacture-montant");
    $titles[] = CAppUI::tr($type_aff ? "CFactureCabinet-_secteur2" : "CFactureCabinet-remise");
    if ($type_aff && $type_view == "consult") {
        $titles[] = CAppUI::tr("CFactureCabinet-_secteur3");
        $titles[] = CAppUI::tr("CFactureCabinet-du_tva");
    }
    $titles[] = CAppUI::tr("Total");
    $titles[] = CAppUI::tr("CReglement-mode");
    $titles[] = CAppUI::tr("CReglement-date-desc");
    $titles[] = CAppUI::tr("CReglement" . ($type_aff ? ".emetteur.patient" : "-montant"));
    if ($type_aff) {
        $titles[] = CAppUI::tr("CReglement.emetteur.tiers");
    }
    $file->writeLine($titles);

    //Tableau
    foreach ($listReglements as $key_date => $_date) {
        foreach ($_date["reglements"] as $_reglement) {
            $facture        = $_reglement->_ref_facture;
            $line_reglement = [
                $facture->_view . ($facture->group_id != $g ? "$facture->_ref_group->_view" : ""),
                $facture->_ref_praticien->_view,
                $facture->_ref_patient->_view,
            ];
            $tarifs         = [];
            switch ($type_view) {
                case "consult":
                    $tarifs = $facture->_ref_consults;
                    break;
                case "evt":
                    $tarifs = $facture->_ref_evts;
                    break;
                case "etab":
                    $tarifs = $facture->_ref_sejours;
                    break;
                default:
            }
            $line_tarif = "";
            foreach ($tarifs as $_tarif) {
                if ($line_tarif) {
                    $line_tarif .= ' - ';
                }
                switch ($type_view) {
                    case "consult":
                        $line_tarif .= CMbDT::format($_tarif->_date, CAppUI::conf("date")) . " " . $_tarif->tarif;
                        break;
                    case "evt":
                        $line_tarif .= $_tarif->tarif;
                        break;
                    case "etab":
                        $line_tarif .= $_tarif->_view;
                        break;
                    default:
                }
            }
            $line_reglement[] = $line_tarif;

            if ($type_aff) {
                $line_reglement[] = $facture->_secteur1;
                $line_reglement[] = $facture->_secteur2;
                if ($type_view == "consult") {
                    $line_reglement[] = $facture->_secteur3;
                    $line_reglement[] = $facture->du_tva;
                }
            } else {
                $line_reglement[] = $facture->_montant_sans_remise;
                $line_reglement[] = $facture->remise;
            }
            $line_reglement[] = $facture->_montant_sans_remise;
            $line_reglement[] = CAppUI::tr("CReglement.mode." . $_reglement->mode);
            $line_reglement[] = CMbDT::format($_reglement->date, CAppUI::conf("date"));
            $line_reglement[] = $_reglement->emetteur == "patient" ? $_reglement->montant : "";
            if ($type_aff) {
                $line_reglement[] = $_reglement->emetteur == "tiers" ? $_reglement->montant : "";
            }
            $file->writeLine($line_reglement);
        }
    }

    $file_name = CAppUI::tr("Compta.print") . "_";
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
