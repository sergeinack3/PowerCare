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
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Labo\CPackExamensLabo;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkEdit();

$pack_examens_labo_id = CView::get("pack_examens_labo_id", "ref class|CPackExamensLabo", true);

CView::checkin();

// Chargement des fontions
$function = new CFunctions();
$listFunctions = $function->loadListWithPerms(PERM_EDIT);

$pack = new CPackExamensLabo();

//Chargement de tous les packs
$where = array(
  "function_id" => "IS NULL OR function_id ".CSQLDataSource::prepareIn(array_keys($listFunctions)),
  "obsolete"    => "= '0'",
);

$listPacks = $pack->loadList($where, "libelle");
CStoredObject::massCountBackRefs($listPacks, "items_examen_labo");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listPacks"           , $listPacks);
$smarty->assign("pack_examens_labo_id", $pack_examens_labo_id);

$smarty->display("inc_list_packs");
