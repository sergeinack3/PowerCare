<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
$object_guid = CValue::getOrSession("object_guid");

/** @var CCodable $objet */
$objet = CMbObject::loadFromGuid($object_guid);
$objet->loadRefsActes();
foreach ($objet->_ref_actes_ccam as &$acte) {
  $acte->loadRefsFwd();
}
$objet->guessActesAssociation();

$sejour = new CSejour();
if ($objet->_class == "CSejour") {
  $sejour = $objet;
}
else {
  $sejour->_id = $objet->sejour_id;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("objet" , $objet);
$smarty->assign("sejour", $sejour);

$smarty->display("inc_confirm_actes_ccam");
