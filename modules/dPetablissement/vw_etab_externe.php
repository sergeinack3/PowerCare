<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CEtabExterne;

CCanDo::checkRead();
$filter   = new CEtabExterne();
$page     = CView::get("page", "num default|0");
$selected = CView::get("selected", "bool default|0");
CView::checkin();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("page"   , $page);
$smarty->assign("filter" , $filter);
$smarty->assign("selected", $selected);
$smarty->display("vw_etab_externe.tpl");

