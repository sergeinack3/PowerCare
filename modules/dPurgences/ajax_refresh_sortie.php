<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkRead();
$user  = CMediusers::get();
$group = CGroups::loadCurrent();

$rpu_id = CView::get("rpu_id", "ref class|CRPU");
$date = CView::get("date", "date default|now", true);
CView::checkin();

$rpu = new CRPU();
$rpu->load($rpu_id);
$rpu->loadRefSejour();
$rpu->loadRefConsult();
$rpu->loadRefMotifSFMU();

$sejour           = $rpu->_ref_sejour;
$sejour->_ref_rpu = $rpu;
$sejour->loadRefsFwd();
$sejour->_ref_rpu->loadRefSejourMutation();
$sejour_mutation = $rpu->_ref_sejour_mutation;
$sejour_mutation->loadRefsAffectations();
$sejour_mutation->loadRefsConsultations();
CAffectation::massUpdateView($sejour_mutation->_ref_affectations);
$_nb_acte_sejour_rpu = 0;
$valide              = true;
foreach ($sejour_mutation->_ref_consultations as $consult) {
  $consult->countActes();
  $_nb_acte_sejour_rpu += $consult->_count_actes;
  if (!$consult->valide) {
    $valide = false;
  }
}
$rpu->_ref_consult->valide     = $valide;
$sejour_mutation->_count_actes = $_nb_acte_sejour_rpu;
foreach ($sejour_mutation->_ref_affectations as $_affectation) {
  if ($_affectation->loadRefService()->urgence) {
    unset($sejour_mutation->_ref_affectations[$_affectation->_id]);
    continue;
  }

  $_affectation->loadView();
}
$sejour->loadNDA();
$sejour->loadRefsConsultations();
$sejour->_ref_rpu->_ref_consult->loadRefsActes();
// Chargement de l'IPP
$sejour->_ref_patient->loadIPP();
$sejour->loadRefCurrAffectation()->loadRefService();

// Chargement des services
$where              = array();
$where["cancelled"] = "= '0'";
$service            = new CService();
$services           = $service->loadGroupList($where);

// Praticiens urgentistes
$listPrats = $user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true);

// Si accès au module PMSI : peut modifier le diagnostic principal
$access_pmsi = 0;
if (CModule::exists("dPpmsi")) {
  $module           = new CModule;
  $module->mod_name = "dPpmsi";
  $module->loadMatchingObject();
  $access_pmsi = $module->getPerm(PERM_EDIT);
}

// Si praticien : peut modifier le CCMU, GEMSA et diagnostic principal
$is_praticien = CAppUI::$user->isPraticien();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("services", $services);
$smarty->assign("listPrats", $listPrats);
$smarty->assign("sejour", $sejour);
$smarty->assign("access_pmsi", $access_pmsi);
$smarty->assign("is_praticien", $is_praticien);
$smarty->assign("date", $date);

$smarty->display("inc_sortie_rpu");
