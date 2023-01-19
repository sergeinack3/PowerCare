<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Ssr\CRHS;

CCanDo::checkEdit();

// RHS concernés
$rhs = new CRHS();
$rhs->load(CValue::get("rhs_id"));

$rhs->loadRefDependances();
$rhs->loadRefSejour();

if (!$rhs->_ref_dependances->_id) {
  $rhs->_ref_dependances->rhs_id = $rhs->_id;
  $rhs->_ref_dependances->store();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("rhs", $rhs);

$smarty->display("inc_edit_dependances_rhs");
