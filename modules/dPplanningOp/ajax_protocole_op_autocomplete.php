<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocoleOperatoire;

CCanDo::checkEdit();

$field          = CView::get("view_field", "str default|libelle");
$libelle        = CView::get($field, "str default|%");
$only_actif     = CView::get("only_actif", "bool default|1");
$only_validated = CView::get("only_validated", "bool default|1");
$chir_id        = CView::get("chir_id", "ref class|CMediusers");
$function_id    = CView::get("function_id", "ref class|CFunctions");
$group_id       = CView::get("group_id", "ref class|CGroups");

CView::checkin();

$owner = [];

// Niveau praticien
$chir = new CMediusers();
$chir->load($chir_id);

if ($chir->_id) {
  $owner[] = "chir_id = '$chir_id'";
}

// Niveau fonction
$function = new CFunctions();
$function->load($chir->function_id ? : $function_id);

if ($function->_id) {
  $owner[] = "function_id = '$function->_id'";
}

// Niveau étabissement
$group = new CGroups();
$group->load($function->group_id ? : $group_id);

if ($group->_id) {
  $owner[] = "group_id = '$group->_id'";
}

// Chargement des protocoles opératoires
$where = [implode(" OR ", $owner)];

if ($only_actif) {
  $where["actif"] = "= '1'";
}

if ($only_validated) {
  // On récupère les protocoles validés par un cadre de bloc
  $where["validation_cadre_bloc_id"] = "IS NOT NULL";
}

$protocole_op = new CProtocoleOperatoire();
$protocoles_op = $protocole_op->seek($libelle, $where);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("protocoles_op", $protocoles_op);

$smarty->display("inc_protocole_op_autocomplete");
