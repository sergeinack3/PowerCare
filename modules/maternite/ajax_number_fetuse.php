<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkEdit();
$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);

$smarty = new CSmartyDP();
$smarty->assign("grossesse", $grossesse);
$smarty->display("inc_number_fetuse");
