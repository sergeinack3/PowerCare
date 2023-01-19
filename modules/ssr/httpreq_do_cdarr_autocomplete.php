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
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CActiviteCdARR;

CCanDo::checkRead();

$needle = CValue::post("code_activite_cdarr", CValue::post("code_cdarr", CValue::post("code")));
if (!$needle) {
  $needle = "%";
}

CView::enableSlave();

$activite = new CActiviteCdARR();
/** @var CActiviteCdARR[] $activites */
$activites = $activite->seek($needle, null, 300);
foreach ($activites as $_activite) {
  $_activite->loadRefTypeActivite();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("activites", $activites);
$smarty->assign("needle", $needle);
$smarty->assign("nodebug", true);

$smarty->display("inc_do_cdarr_autocomplete");
