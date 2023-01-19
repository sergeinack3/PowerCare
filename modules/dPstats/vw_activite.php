<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkEdit();

$debutact = CValue::getOrSession("debutact", CMbDT::date());
$finact   = CValue::getOrSession("finact", CMbDT::date());

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("debutact", $debutact);
$smarty->assign("finact", $finact);

$smarty->display("vw_activite.tpl");
