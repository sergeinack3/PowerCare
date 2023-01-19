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
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;

CCanDo::checkEdit();
$evenement_guid = CView::get("evenement_guid", "str");
CView::checkin();

/* @var CEvenementPatient $evenement*/
$evenement = CMbObject::loadFromGuid($evenement_guid);
$evenement->isCoded();
$evenement->countActes();
$evenement->loadRefPraticien();
$evenement->loadExtCodesCCAM();
$evenement->getAssociationCodesActes();
$evenement->loadPossibleActes();
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

$user = CMediusers::get();
$user->isPraticien();
$user->isProfessionnelDeSante();

$listChirs = CConsultation::loadPraticiens(PERM_EDIT);
$listAnesths = $user->loadAnesthesistes(PERM_DENY);

$divers = array();
if (CAppUI::gconf("dPccam frais_divers use_frais_divers_CEvenementPatient")) {
  $divers = $evenement->loadRefsFraisDivers(count($evenement->_ref_factures)+1);
  $evenement->loadRefsFraisDivers(null);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("frais_divers", $divers);
$smarty->assign("evenement", $evenement);
$smarty->assign("user"     , $user);
$smarty->assign("listChirs", $listChirs);
$smarty->assign('listAnesths', $listAnesths);
$smarty->assign("acte_ngap", CActeNGAP::createEmptyFor($evenement));
$smarty->assign("reglement", new CReglement());

$smarty->display("vw_edit_facture_evt");