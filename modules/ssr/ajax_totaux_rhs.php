<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Ssr\CRHS;

CCanDo::checkEdit();

// Séjour concernés
$rhs = new CRHS();
$rhs->load(CValue::get("rhs_id"));
if (!$rhs->_id) {
  CAppUI::stepAjax("RHS inexistant", UI_MSG_ERROR);
}
$rhs->loadRefsNotes();
$rhs->buildTotaux();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("rhs", $rhs);

$smarty->display("inc_totaux_rhs");
