<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;

CCanDo::checkEdit();

$evenement_guid = CValue::get("evenement_guid");

/* @var CEvenementPatient $evenement*/
$evenement = CMbObject::loadFromGuid($evenement_guid);
$evenement->loadRefPraticien()->loadRefFunction();
$evenement->loadRefPatient();
$evenement->loadRefFacture();
$evenement->loadRefsActes();

$evenement->isCoded();
$evenement->countActes();
$evenement->loadRefPraticien();
$evenement->loadExtCodesCCAM();
$evenement->getAssociationCodesActes();
//$evenement->loadPossibleActes();
$evenement->canDo();

$evenement->loadRefsCodagesCCAM();
foreach ($evenement->_ref_codages_ccam as $_codages_by_prat) {
  foreach ($_codages_by_prat as $_codage) {
    $_codage->loadPraticien()->loadRefFunction();
    $_codage->loadActesCCAM();
    $_codage->getTarifTotal();
    foreach ($_codage->_ref_actes_ccam as $_acte) {
      $_acte->getTarif();
    }
  }
}

//Recherche de la facture pour cette consultation
$facture = $evenement->_ref_facture;
if ($facture->_id) {
  $facture->loadRefPatient();
  $facture->_ref_patient->loadRefsCorrespondantsPatient("date_debut DESC, date_fin DESC");
  $facture->loadRefPraticien();
  $facture->loadRefAssurance();
  $facture->loadRefsObjects();
  $facture->loadRefsReglements();
  $facture->loadRefsItems();
  $facture->loadRefsNotes();
  $facture->loadRefCoeff();
  $facture->loadCoefficients();
  $facture->loadRefCategory();
}

// Récupération des tarifs
$tarifs = array();
if (!$evenement->tarif || $evenement->tarif == "pursue") {
  $tarifs = CTarif::loadTarifsUser($evenement->_ref_praticien);
}

$user = CMediusers::get();
$user->isPraticien();
$user->isProfessionnelDeSante();

$listChirs = CConsultation::loadPraticiens(PERM_EDIT);

$divers = array();
if (CAppUI::gconf("dPccam frais_divers use_frais_divers_CEvenementPatient")) {
  $divers = $evenement->loadRefsFraisDivers(count($evenement->_ref_factures)+1);
  $evenement->loadRefsFraisDivers(null);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("frais_divers"  , $divers);
$smarty->assign("evenement", $evenement);
$smarty->assign("facture"  , $facture);
$smarty->assign("tarifs"   , $tarifs);
$smarty->assign("user"     , $user);
$smarty->assign("listChirs", $listChirs);
$smarty->assign("reglement", new CReglement());

$smarty->display("vw_facture_evt");