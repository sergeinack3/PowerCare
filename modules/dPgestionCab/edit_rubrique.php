<?php
/**
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\GestionCab\CRubrique;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$user = CMediusers::get();
$user->loadRefsFwd();
$user->_ref_function->loadRefsFwd();

$etablissement = $user->_ref_function->_ref_group->text;

$rubrique_id = CValue::get("rubrique_id");
 
$rubrique = new CRubrique();
$rubrique->load($rubrique_id);

// Récupération de la liste des functions
$function = new CFunctions();
$listFunc = $function->loadListWithPerms(PERM_EDIT);

$where        = array();
$itemRubrique = new CRubrique;
$order        = "nom DESC";
 
// Récupération de la liste des rubriques hors fonction
$where["function_id"] = "IS NULL";
$listRubriqueGroup = $itemRubrique->loadList($where, $order);
 
$listRubriqueFonction = array();

// Récupération de la liste des rubriques liés aux fonctions
foreach ($listFunc as $function) {
  $where["function_id"] = "= '$function->function_id'";
  $listRubriqueFonction[$function->text] = $itemRubrique->loadList($where, $order);
}

$smarty = new CSmartyDP();

$smarty->assign("etablissement",        $etablissement);
$smarty->assign("listFunc",             $listFunc);
$smarty->assign("rubrique",             $rubrique);
$smarty->assign("listRubriqueGroup",    $listRubriqueGroup);
$smarty->assign("listRubriqueFonction", $listRubriqueFonction);

$smarty->display("edit_rubrique");
