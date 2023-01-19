<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Atih\CUniteMedicaleInfos;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CUniteMedicale;

CCanDo::checkAdmin();
$uf_id = CValue::getOrSession("uf_id");

$group          = CGroups::loadCurrent();
$etablissements = CMediusers::loadEtablissements(PERM_READ);
$praticiens     = CAppUI::$user->loadPraticiens();

// Chargement de l'uf à ajouter/éditer
$uf           = new CUniteFonctionnelle();
$uf->group_id = $group->_id;
$uf->load($uf_id);
$uf->loadRefUm();
$uf->loadRefsNotes();

// Récupération des ufs
$order = "group_id, code";
$ufs   = array(
  "hebergement" => $uf->loadGroupList(array("type" => "= 'hebergement'"), $order),
  "medicale"    => $uf->loadGroupList(array("type" => "= 'medicale'"), $order),
  "soins"       => $uf->loadGroupList(array("type" => "= 'soins'"), $order),
);

// Récupération des Unités Médicales (pmsi)
$ums       = array();
$ums_infos = array();
$um        = new CUniteMedicale();
if (CSQLDataSource::get("sae") && CModule::getActive("atih")) {
  $um_infos            = new CUniteMedicaleInfos();
  $ums                 = $um->loadListUm();
  $group               = CGroups::loadCurrent();
  $where["group_id"]   = " = '$group->_id'";
  $where["mode_hospi"] = " IS NOT NULL";
  $where["nb_lits"]    = " IS NOT NULL";
  $ums_infos           = $um_infos->loadList($where);
}


$smarty = new CSmartyDP();

$smarty->assign("praticiens", $praticiens);
$smarty->assign("etablissements", $etablissements);
$smarty->assign("ufs", $ufs);
$smarty->assign("uf", $uf);
$smarty->assign("ums", $ums);
$smarty->assign("ums_infos", $ums_infos);
$smarty->display('inc_display_ufs.tpl');
