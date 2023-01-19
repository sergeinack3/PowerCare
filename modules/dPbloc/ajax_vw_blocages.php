<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;

/**
 * dPbloc
 */
$blocage_id = CView::get("blocage_id", 'ref class|CBlocage', true);
CView::checkin();

$smarty = new CSmartyDP;

$smarty->assign("blocage_id", $blocage_id);

$smarty->display("inc_vw_blocages.tpl");
