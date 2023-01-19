<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Facturation\CRelance;

CCanDo::checkEdit();
$relance_id = CValue::get("relance_id");

$relance = new CRelance();
$relance->load($relance_id);

// Creation du template
$smarty = new CSmartyDP();

$smarty->assign("relance" , $relance);

$smarty->display("vw_edit_relance");