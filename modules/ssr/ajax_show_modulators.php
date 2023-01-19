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

$code_activite_csarr = CValue::get("code_activite_csarr");

$activite_csarr = CActiviteCsARR::get($code_activite_csarr);
$activite_csarr->loadRefsModulateurs();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("activite_csarr", $activite_csarr);
$smarty->display("inc_show_modulators");