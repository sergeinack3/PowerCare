<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
$date_min         = CView::get("_date_min", "str default|01/01/1970", true);
$date_max         = CView::get("_date_max", "date default|now", true);
$etat             = CView::get("etat", "str default|ouvert", true);
$facture_class    = CView::get("facture_class", "enum list|CFactureCabinet|CFactureEtablissement default|CFactureCabinet", true);
$facture          = new $facture_class;
$facture_id       = CView::get("facture_id", "str", true);
$patient_id       = CView::get("patient_id", "ref class|CPatient", true);
$type_date_search = CView::get("type_date_search", "enum list|cloture|ouverture default|cloture", true);
$chirSel          = CView::get("chirSel", "str", true);
$num_facture      = CView::get("num_facture", "str", true);
$numero           = CView::get("numero", "enum list|0|1|2|3 default|1", true);
$search_easy      = CView::get("search_easy", "str default|0", true);
$page             = CView::get("page", "num default|0");
$export_csv       = CView::get("export_csv", "num default|0");
$xml_etat         = CView::get("xml_etat", "enum list|echec|non_envoye|envoye", true);
$type_facture     = CView::get("type_facture", "enum list|maladie|accident|esthetique", true);
$montant_total    = CView::get("montant_total", "float");
$statut_pro       = CView::get("statut_pro", "enum list|" . $facture->_specs["statut_pro"]->list, true);
$bill_printed     = CView::get("bill_printed", "enum list|-1|0|1 default|-1", true);
$justif_printed   = CView::get("justif_printed", "enum list|-1|0|1 default|-1", true);

CView::checkin();
CView::enforceSlave();

$use_auto_cloture = CAppUI::gconf("dPfacturation $facture_class use_auto_cloture");
$facture_table    = $facture_class == "CFactureCabinet" ? "facture_cabinet" : "facture_etablissement";

$ljoin                      = [];
$where                      = [];
$where["group_id"]          = "= '" . CGroups::loadCurrent()->_id . "'";
$where["$type_date_search"] = "BETWEEN '$date_min' AND '$date_max'";

if ($montant_total) {
    $where["$facture_table.montant_total"] = " = '$montant_total'";
}

$ljoin["facture_liaison"]           = "facture_liaison.facture_class = '$facture_class' AND facture_liaison.facture_id = $facture_table.facture_id";
$where["facture_liaison.object_id"] = "IS NOT NULL";

if (in_array("2", $search_easy)) {
    $where[] = "$facture_table.cloture IS NOT NULL";
} elseif (in_array("3", $search_easy) && $type_date_search != "cloture") {
    $where[] = "$facture_table.cloture IS NULL";
} elseif (in_array("4", $search_easy)) {
    $ljoin["factureitem"] = "factureitem.object_class = '$facture_class' AND factureitem.object_id = $facture_table.facture_id";
    if ($use_auto_cloture) {
        $where[] = "$facture_table.cloture IS NOT NULL AND factureitem.factureitem_id IS NULL";
    } else {
        $where[] = "($facture_table.cloture IS NOT NULL AND factureitem.factureitem_id IS NULL) OR $facture_table.cloture IS NULL";
    }
} elseif (in_array("5", $search_easy)) {
    $where["annule"] = " = '1'";
} elseif (in_array("6", $search_easy)) {
    $where["patient_date_reglement"] = "IS NOT NULL";
} elseif (in_array("7", $search_easy)) {
    if ($numero) {
        $where["facture_relance.numero"] = " = '$numero'";
    }
    $ljoin["facture_relance"]              = "facture_relance.object_id = $facture_table.facture_id";
    $where["facture_relance.object_class"] = " = '$facture_class'";
} elseif (in_array("8", $search_easy)) {
    $ljoin["facture_rejet"]        = "facture_rejet.facture_class = '$facture_class' AND facture_rejet.facture_id = $facture_table.facture_id";
    $where["facture_rejet.statut"] = " = 'attente' ";
} elseif (in_array("9", $search_easy)) {
    $where[] = "($facture_table.du_patient <> '0' AND patient_date_reglement IS NULL) OR ($facture_table.du_tiers <> '0' AND tiers_date_reglement IS NULL)";
}

if (!in_array("4", $search_easy) && !in_array("5", $search_easy) && !in_array("0", $search_easy)) {
    $where[] = "$facture_table.du_patient <> '0' OR $facture_table.du_tiers <> '0'";
}

if (!$chirSel || $chirSel == -1) {
    $user                                 = new CMediusers();
    $listChir                             = $user->loadPraticiens(PERM_EDIT);
    $where["$facture_table.praticien_id"] = CSQLDataSource::prepareIn(array_keys($listChir));
} elseif ($chirSel) {
    $where["$facture_table.praticien_id"] = " = '$chirSel' ";
}
if ($patient_id) {
    $where["$facture_table.patient_id"] = " = '$patient_id' ";
}
if ($num_facture) {
    $where["$facture_table.facture_id"] = " = '$num_facture' ";
}
if ($numero) {
    $where["$facture_table.numero"] = " = '$numero'";
}
if ($xml_etat != "") {
    $where["$facture_table.statut_envoi"] = " = '$xml_etat' ";
}
if ($type_facture) {
    $where["$facture_table.type_facture"] = " = '$type_facture' ";
}
if ($statut_pro) {
    $where["$facture_table.statut_pro"] = " = '$statut_pro'";
}

if ($bill_printed !== "-1") {
    $where["bill_date_printed"] = "IS " . ($bill_printed === "0" ? "" : "NOT ") . "NULL";
}
if ($justif_printed !== "-1") {
    $where["justif_date_printed"] = "IS " . ($justif_printed === "0" ? "" : "NOT ") . "NULL";
}

$limit          = !$export_csv ? "$page, 25" : null;
$factures       = $facture->loadList($where, "ouverture ASC, numero", $limit, "facture_id", $ljoin);
$total_factures = count(array_unique($facture->loadColumn("$facture_table.facture_id", $where, $ljoin)));

CStoredObject::massLoadFwdRef($factures, "patient_id");
foreach ($factures as $key => $_facture) {
    /** @var CFacture $_facture */
    $_facture->loadRefPatient();
    $_facture->loadRefsObjects();
    $_facture->loadRefsReglements();
    $_facture->loadRefAssurancePatient();
    $_facture->loadStatut();
    $_facture->loadRefsRejets();
    //Pour la cloture manuelle, il faut compter les actes après chargement des factures lorsqu'elles ne sont pas cloturées
    $nb_ngap   = count($_facture->_ref_actes_ngap);
    $nb_ccam   = count($_facture->_ref_actes_ccam);
    if (!$_facture->cloture && !$use_auto_cloture && $search_easy == 4 && ($nb_ngap != 0 || $nb_ccam != 0)) {
        unset($factures[$key]);
        $total_factures = $total_factures - 1;
        continue;
    }
}

if (!$export_csv) {
    // Création du template
    $smarty = new CSmartyDP();

    $smarty->assign("factures", $factures);
    $smarty->assign("facture", $facture);
    $smarty->assign("page", $page);
    $smarty->assign("total_factures", $total_factures);
    $smarty->assign("print", 0);

    $smarty->display("inc_list_factures.tpl");
} else {
    $use_bill_ch = 0;
    $columns     = [
        CAppUI::tr("Date"),
        CAppUI::tr("CFactureCabinet-numero"),
        CAppUI::tr("CPatient-nom"),
        CAppUI::tr("CPatient-prenom"),
    ];

    if ($use_bill_ch) {
        array_push(
            $columns,
            CAppUI::tr("CFactureCabinet-_type_rbt"),
            CAppUI::tr("CFacture-_debiteur"),
            CAppUI::tr("CFactureCabinet-type_facture")
        );
    }

    if ($facture->_class == "CFactureEtablissement") {
        array_push($columns, CAppUI::tr("CSejour-date"));
    } else {
        array_push($columns, CAppUI::tr("CConsultation-date"));
    }

    array_push(
        $columns,
        CAppUI::tr("CFactureCabinet-amount-invoice"),
        CAppUI::tr("CFactureCabinet-amount-paid"),
        CAppUI::tr("CFactureCabinet-amount-unpaid")
    );

    if ($use_bill_ch) {
        array_push($columns, CAppUI::tr("CFactureCabinet-send-xml-or-paper"));
    }
    array_push(
        $columns,
        CAppUI::tr("Status"),
        CAppUI::tr("common-Practitioner")
    );

    $csv = new CCSVFile();
    $csv->writeLine($columns);

    foreach ($factures as $_facture) {
        $row = [
            $_facture->ouverture,
            $_facture->_view,
            $_facture->_ref_patient->nom,
            $_facture->_ref_patient->prenom,
        ];

        if ($use_bill_ch) {
            array_push(
                $row,
                $_facture->_type_rbt,
                $_facture->_assurance_patient_view,
                $_facture->statut_pro == "enceinte" ? "Grossesse" : $_facture->type_facture
            );
        }

        array_push(
            $row,
            $_facture->_ref_last_consult->_date,
            $_facture->_montant_avec_remise,
            $_facture->_reglements_total,
            $_facture->_du_restant
        );

        if ($use_bill_ch) {
            if ($_facture->request_date) {
                array_push($row, $_facture->request_date);
            } else {
                array_push($row, $_facture->statut_envoi ? "oui" : "non");
            }
        }
        $statuts = [];
        foreach ($_facture->_statut as $_statut) {
            $statuts[] = CAppUI::tr($_statut);
        }
        array_push(
            $row,
            implode(',', $statuts),
            $_facture->_ref_praticien
        );
        $csv->writeLine($row);
    }

    $csv->stream("Liste des factures du " . CMbDT::format($date_min, "%d-%m-%Y") . " au " . CMbDT::format($date_max, "%d-%m-%Y"), true);
}
