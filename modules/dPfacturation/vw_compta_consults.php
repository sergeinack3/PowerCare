<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\CTarif;

CCanDo::checkEdit();
// Récupération des paramètres
$filter            = new CPlageconsult();
$filter->_date_min = CView::get("_date_min", "date default|now", true);
$filter->_date_max = CView::get("_date_max", "date default|now", true);
$chir_id           = CView::get("chir", "ref class|CMediusers", true);
$tarif_id          = CView::get("tarif", "str", true);
$category_id       = CView::get("category_id", "ref class|CFactureCategory", true);
$lieu_id           = CView::get("lieu", "ref class|CLieuConsult");

CView::checkin();
CView::enableSlave();

// Filtre sur les praticiens
$listPrat = CConsultation::loadPraticiensCompta($chir_id);

// On recherche toutes les consultations sur la période choisie
$ljoin                 = [];
$ljoin["consultation"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";

$where                            = [];
$where["consultation.patient_id"] = " IS NOT NULL";
$where["plageconsult.chir_id"]    = CSQLDataSource::prepareIn(array_keys($listPrat));
$where["plageconsult.date"]       = "BETWEEN '$filter->_date_min' AND '$filter->_date_max'";

// Tarifs
$tarif = CTarif::find($tarif_id);
if ($tarif) {
    $where["consultation.tarif"] = " = '$tarif->description'";
}

if ($category_id) {
    $ljoin["facture_liaison"] = "facture_liaison.object_id = consultation.consultation_id";
    $ljoin["facture_cabinet"] = "facture_liaison.facture_id = facture_cabinet.facture_id";
    $where["category_id"]     = " = '$category_id'";
}

$order = "plageconsult.date, plageconsult.chir_id";

$plage = new CPlageconsult();
/** @var CPlageconsult[] $plages */
$plages = $plage->loadList($where, $order, null, "plageconsult.plageconsult_id", $ljoin);

$listConsults_date = $factures = [];
$recapReglement    = [
    "facture"   => "0",
    "regle"     => "0",
    "non_regle" => "0",
];

CStoredObject::massLoadFwdRef($plages, "chir_id");
CStoredObject::massLoadFwdRef($plages, "pour_compte_id");
$consultations = CStoredObject::massLoadBackRefs($plages, "consultations");
CStoredObject::massLoadFwdRef($consultations, "patient_id");
foreach ($plages as $key => $_plage) {
    $_plage->_total = [
        "facture" => 0,
        "regle"   => 0,
    ];
    $_plage->loadRefChir()->loadRefFunction();
    $_plage->loadRefPourCompte();
    $_plage->loadRefsConsultations();
    foreach ($_plage->_ref_consultations as $_consult) {
        $_consult->loadRefPlageConsult();
        $agenda = $_consult->_ref_plageconsult->loadRefAgendaPraticien();
        if ($lieu_id && ($lieu_id != $agenda->lieuconsult_id || !$agenda)) {
            unset($plages[$key]);
            continue 2;
        }
        $_consult->loadRefPatient();
        $facture = $_consult->loadRefFacture();
        if ($facture->_id && !isset($factures[$facture->_guid])) {
            $facture->loadRefGroup();
            $facture->loadRefsConsultation();
            $facture->loadRefCoeffFacture();
            $facture->updateMontants();
            $facture->loadRefsReglements();
            $facture->loadRefsNotes();

            $recapReglement["facture"]   += $facture->_montant_avec_remise;
            $recapReglement["regle"]     += $facture->_reglements_total;
            $recapReglement["non_regle"] += $facture->_du_restant;
            $factures[$facture->_guid]   = 1;
            $_plage->_total["facture"]   += $facture->_montant_avec_remise;
            $_plage->_total["regle"]     += $facture->_reglements_total;
        }
        if (!$facture->_id) {
            unset($_plage->_ref_consultations[$_consult->_id]);
        }
    }
    if (!count($_plage->_ref_consultations)) {
        unset($plages[$_plage->_id]);
    }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("recapReglement", $recapReglement);
$smarty->assign("filter", $filter);
$smarty->assign("listPrat", $listPrat);
$smarty->assign("plages", $plages);
$smarty->assign("tarif", $tarif);

$smarty->display("vw_compta_consults");
