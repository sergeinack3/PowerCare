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
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
$date_min         = CView::get("_date_min", ['date', 'default' => CMbDT::date()], true);
$date_max         = CView::get("_date_max", ['date', 'default' => CMbDT::date()], true);
$all_group_compta = CView::get("_all_group_compta", 'bool default|1', true);
$type_view        = CView::get("type_view", 'enum list|consult|etab|evt default|consult', true);
$prat             = CView::getRefCheckRead("chir", 'ref class|CMediusers');
$function_compta  = CView::get('function_compta', 'bool default|0');
$category_id      = CView::get("category_id", "ref class|CFactureCategory", true);
$lieu_id          = CView::get("lieu", "ref class|CLieuConsult");

CView::enableSlave();
CView::checkin();

// Chargement du praticien
$praticien = CMediusers::get($prat);
$praticien->loadRefBanque();
$function = $praticien->loadRefFunction();
if (!$praticien->_id) {
    CAppUI::stepMessage(UI_MSG_WARNING, "CMediusers-warning-undefined");

    return;
}

// Extraction des elements qui composent le numero de compte
$compte_banque  = substr($praticien->compte, 0, 5);
$compte_guichet = substr($praticien->compte, 5, 5);
$compte_numero  = substr($praticien->compte, 10, 11);
$compte_cle     = substr($praticien->compte, 21, 2);

// Montant total des cheques
$montantTotal = 0;

$view_etab     = $type_view == "etab";
$table_facture = $view_etab ? "facture_etablissement" : "facture_cabinet";

$ljoin                 = [];
$ljoin[$table_facture] = "$table_facture.facture_id = reglement.object_id";

$where                 = [];
$where["object_class"] = $view_etab ? " = 'CFactureEtablissement'" : " = 'CFactureCabinet'";

if ($function_compta && $function->compta_partagee) {
    $users = $function->loadRefsUsers(
        ['Chirurgien', 'Anesthésiste', 'Médecin', 'Dentiste', 'Infirmière', 'Rééducateur', 'Sage Femme', 'Diététicien']
    );

    $where["$table_facture.praticien_id"] = CSQLDataSource::prepareIn(array_keys($users));
} else {
    $where["$table_facture.praticien_id"] = "= '$praticien->_id'";
}
if ($category_id) {
    $where["$table_facture.category_id"] = " = '$category_id'";
}

if (!$all_group_compta) {
    $where["$table_facture.group_id"] = "= '" . CGroups::loadCurrent()->_id . "'";
}
$where['reglement.mode'] = "= 'cheque' ";
$where['reglement.date'] = "BETWEEN '$date_min' AND '$date_max 23:59:59' ";
$order                   = "reglement.date ASC";

// Chargement des règlements via les factures
$reglement  = new CReglement();
$reglements = $reglement->loadList($where, $order, null, "reglement.reglement_id", $ljoin);

// Chargements des consultations
$montantTotal = 0.0;
CStoredObject::massLoadFwdRef($reglements, "banque_id");
foreach ($reglements as $key => $_reglement) {
    $_reglement->loadRefFacture();
    $agenda = $_reglement->_ref_facture->_ref_last_consult->loadRefPlageConsult()->loadRefAgendaPraticien();
    if ($lieu_id && ($lieu_id != $agenda->lieuconsult_id || !$agenda)) {
        unset($reglements[$key]);
        continue;
    }

    /** @var CReglement $_reglement */
    $facture = $_reglement->loadTargetObject();
    $facture->loadRefPatient();
    $_reglement->loadRefBanque();
    $facture->loadRefsObjects();
    if (($type_view == "evt" && !count($facture->_ref_evts)) ||
        (!count($facture->_ref_consults) && $type_view == "consult")) {
        unset($reglements[$_reglement->_id]);
        continue;
    }
    $montantTotal += $_reglement->montant;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("praticien", $praticien);
$smarty->assign('view_function', $function_compta);
$smarty->assign("reglements", $reglements);
$smarty->assign("compte_banque", $compte_banque);
$smarty->assign("compte_guichet", $compte_guichet);
$smarty->assign("compte_numero", $compte_numero);
$smarty->assign("compte_cle", $compte_cle);
$smarty->assign("montantTotal", $montantTotal);

$smarty->display("print_bordereau");
