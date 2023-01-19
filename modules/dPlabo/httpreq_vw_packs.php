<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Labo\CPackExamensLabo;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();
$user = CMediusers::get();

$pack_examens_labo_id = CValue::getOrSession("pack_examens_labo_id");
$typeListe            = CValue::getOrSession("typeListe");
$dragPacks            = CValue::get("dragPacks", 0);

// Chargement des fontions
$function = new CFunctions();
$listFunctions = $function->loadListWithPerms(PERM_EDIT);

// Chargement du pack demandé
$pack = new CPackExamensLabo();
$pack->load($pack_examens_labo_id);
$pack->loadRefs();

//Chargement de tous les packs
$where = array("function_id IS NULL OR function_id ".CSQLDataSource::prepareIn(array_keys($listFunctions)));
$where["obsolete"] = " = '0'";

$order = "libelle";
$listPacks = $pack->loadList($where, $order);
foreach ($listPacks as $key => $curr_pack) {
  $listPacks[$key]->loadRefs();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listPacks", $listPacks);
$smarty->assign("pack"     , $pack     );
$smarty->assign("dragPacks", $dragPacks);

$smarty->display("inc_vw_packs");
