<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\CGrossesseAnt;

CCanDo::checkEdit();

$grossesse_ant_id = CValue::get("grossesse_ant_id");
$grossesse_id     = CValue::get("grossesse_id");

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$patient = $grossesse->loadRefParturiente();

$grossesseAnt = new CGrossesseAnt();
if (!$grossesseAnt->load($grossesse_ant_id)) {
  $grossesseAnt->grossesse_id = $grossesse_id;
}

$smarty = new CSmartyDP();

$smarty->assign("grossesse", $grossesse);
$smarty->assign("patient", $patient);
$smarty->assign("grossesseAnt", $grossesseAnt);

$smarty->display("edit_grossesse_ant.tpl");

