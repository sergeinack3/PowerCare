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
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CUniteMedicale;

CCanDo::checkAdmin();

$use_uf         = CValue::get("uf_id");
$uf_id          = CValue::getOrSession("uf_id");
$use_prestation = CValue::get("prestation_id");
$uf_type        = CValue::get("uf_type");
$prestation_id  = CValue::getOrSession("prestation_id");
$group          = CGroups::loadCurrent();

// Liste des Etablissements
$etablissements = CMediusers::loadEtablissements(PERM_READ);
$praticiens     = CAppUI::$user->loadPraticiens();

if ($use_uf != null) {
  // Chargement de l'uf à ajouter/éditer
  $uf           = new CUniteFonctionnelle();
  $uf->group_id = $group->_id;
  $uf->load($uf_id);
  $uf->loadRefUm();
  $uf->loadRefsNotes();
  $uf->type = ($uf_type) ?: null;

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

  // Récupération des ufs
  $order = "group_id, code";
  $ufs   = $uf->loadList(null, $order);
}

if ($use_prestation != null) {
  // Chargement de la prestation à ajouter/éditer
  $prestation           = new CPrestation();
  $prestation->group_id = $group->_id;
  $prestation->load($prestation_id);
  $prestation->loadRefsNotes();

  // Récupération des prestations
  $order = "group_id, nom";
  /** @var CPrestation[] $prestations */
  $prestations = $prestation->loadList(null, $order);
  foreach ($prestations as $_prestation) {
    $_prestation->loadRefGroup();
    $_prestation->loadRefsNotes();
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("praticiens", $praticiens);
$smarty->assign("etablissements", $etablissements);

if ($use_uf != null) {
  $smarty->assign("uf", $uf);
  $smarty->assign("ums", $ums);
  $smarty->assign("ums_infos", $ums_infos);
  $smarty->assign("type_ud_pmsi", CAppUI::gconf("atih uf uf_pmsi"));
  $smarty->display("inc_vw_uf.tpl");
}
elseif ($use_prestation != null) {
  $smarty->assign("prestation", $prestation);
  $smarty->display("inc_vw_prestation.tpl");
}
