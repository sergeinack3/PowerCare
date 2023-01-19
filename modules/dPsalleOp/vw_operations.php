<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

// Ne pas supprimer, utilisé pour mettre le praticien en session
$praticien_id  = CView::get("praticien_id", "ref class|CMediusers", true);
$hide_finished = CView::get("hide_finished", "bool default|0", true);
$salle_id      = CView::get("salle", "ref class|CSalle", true);
$operation_id  = CView::get("operation_id", "ref class|COperation", true);
$date          = CView::get("date", "date default|now", true);
$fragment      = CView::get("fragment", "str");

// Sauvegarde en session du bloc (pour preselectionner dans la salle de reveil)
$salle = new CSalle();
$salle->load($salle_id);
if ($salle->_id) {
  $salle->loadRefBloc();
  /* Reset the session data if the data in session are not from the current group */
  if ($salle->_ref_bloc->group_id == CGroups::loadCurrent()->_id) {
    CView::setSession("bloc_id", $salle->bloc_id);
  }
  else {
    CView::setSession('bloc_id');
    CView::setSession('salle');
    CView::setSession('operation_id');
  }
}

CView::checkin();

CAccessMedicalData::logAccess("COperation-$operation_id");

// Récuperation de la salle à afficher par défaut
$default_salles_id = CAppUI::pref("default_salles_id");
$default_salles_id = json_decode($default_salles_id);
$group_id = CGroups::loadCurrent()->_id;
if (!$salle_id && isset($default_salles_id->{"g$group_id"})) {
  $salles = explode("|", $default_salles_id->{"g$group_id"});
  $salle_id = reset($salles);
}

// Récupération de l'utilisateur courant
$currUser = CMediusers::get();
$currUser->isAnesth();
$currUser->isPraticien();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("operation_id" , $operation_id);
$smarty->assign("salle"        , $salle_id);
$smarty->assign("currUser"     , $currUser);
$smarty->assign("date"         , $date);
$smarty->assign("fragment"     , $fragment);
$smarty->assign("hide_finished", $hide_finished);

$smarty->display("vw_operations.tpl");
