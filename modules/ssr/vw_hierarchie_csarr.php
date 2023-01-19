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
use Ox\Mediboard\Ssr\CHierarchieCsARR;

CCanDo::checkRead();

$code       = CValue::get("code");
$hierarchie = CHierarchieCsARR::get($code);
$hierarchie->loadRefsNotesHierarchies();
$hierarchie->loadRefsParentHierarchies();
$hierarchie->loadRefsChildHierarchies();
$hierarchie->loadRefsActivites();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("hierarchie", $hierarchie);

$smarty->display("vw_hierarchie_csarr");
