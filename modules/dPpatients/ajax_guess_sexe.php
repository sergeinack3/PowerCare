<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$object_class = CView::get("object_class", "str");
$assure       = CView::get("assure", "bool");
$limit        = CView::get("limit", "num default|100");

CView::checkin();

if (!$object_class) {
  CAppUI::stepMessage(UI_MSG_WARNING, "Veuillez choisir une classe !");
  CApp::rip();
}

$object = new $object_class();

$field_prenom = $object->getPrenomFieldName();
$field_sexe   = $object->getSexFieldName();

// Cas particulier de l'assuré qui est stocké dans la même table que le patient...
if ($assure) {
  $field_prenom = "assure_prenom";
  $field_sexe   = "assure_sexe";
}

$where = array(
  $field_prenom => "IS NOT NULL",
  $field_sexe   => "IS NULL OR $field_sexe = 'u'"
);

$total = $object->countList($where);

$objects = $object->loadList($where, $field_prenom, $limit);

$smarty = new CSmartyDP();

$smarty->assign("objects", $objects);
$smarty->assign("total", $total);
$smarty->assign("field_prenom", $field_prenom);
$smarty->assign("field_sexe", $field_sexe);
$smarty->assign("object_class", $object_class);

$smarty->display("inc_guess_sexe.tpl");