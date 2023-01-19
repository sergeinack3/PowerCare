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
use Ox\Mediboard\Ssr\CActiviteCsARR;

CCanDo::checkRead();

$code     = CValue::get("code");
$activite = CActiviteCsARR::get($code);
$activite->loadRefsNotesActivites();
$activite->loadRefsGestesComplementaires();
$activite->loadRefsHierarchies();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("activite", $activite);

$smarty->display("csarr/inc_activite");
