<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CDiscipline;

/**
 * View disciplines
 */
CCanDo::checkRead();

$page = CView::get('page', 'num default|0');
CView::checkin();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("page" , $page);
$smarty->assign("specialite", new CDiscipline());
$smarty->display("vw_idx_disciplines.tpl");

