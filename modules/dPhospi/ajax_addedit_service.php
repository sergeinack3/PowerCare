<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$now = CMbDT::dateTime();

$service_id = CValue::getOrSession("service_id");
$group      = CGroups::loadCurrent();
// Liste des Etablissements
$etablissements = CMediusers::loadEtablissements(PERM_READ);

// Chargement du service à ajouter/editer
$service           = new CService();
$service->group_id = $group->_id;
$service->load($service_id);
$service->loadRefsNotes();

// get nb of patients using this service
if ($service->_id) {
  $sejour                 = new CSejour();
  $where                  = array();
  $where["service_id"]    = " = '$service->_id'";
  $where["sortie_prevue"] = "> '$now'";
  $where["annule"]        = "!= '1'";
  $nb_sejours             = $sejour->countList($where);
  CAppUI::stepAjax("Service-msg-%d_use_this_service", $nb_sejours ? UI_MSG_WARNING : UI_MSG_OK, $nb_sejours);
}

$praticiens = CAppUI::$user->loadPraticiens();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("praticiens", $praticiens);
$smarty->assign("etablissements", $etablissements);
$smarty->assign("service", $service);
$smarty->assign("tag_service", CService::getTagService($group->_id));
$smarty->display("inc_vw_service.tpl");