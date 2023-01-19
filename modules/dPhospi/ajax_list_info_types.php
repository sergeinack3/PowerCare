<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CInfoType;

$modal = CView::get('modal', 'bool default|0');

CView::checkin();

$types = CInfoType::loadForUser();
foreach ($types as $type) {
  $type->countInfos();
}

$smarty = new CSmartyDP();
$smarty->assign('types', $types);
$smarty->assign('modal', $modal);
$smarty->display('inc_list_info_types.tpl');