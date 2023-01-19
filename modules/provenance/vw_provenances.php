<?php
/**
 * @package Mediboard\Provenance
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Provenance\CProvenance;
use Ox\Core\Module\CModule;

CCanDo::checkAdmin();

$group_id  = CGroups::loadCurrent()->_id;
$order_col = CView::get('order_col', 'str default|libelle', true);
$order_way = CView::get('order_way', 'str default|ASC', true);

CView::checkin();

$provenance        = new CProvenance();
$where['group_id'] = "= $group_id";
$order             = "$order_col $order_way";
$provenances       = $provenance->loadList($where, $order);

$smarty = new CSmartyDP();
$smarty->assign('group_id', $group_id);
$smarty->assign('provenances', $provenances);
$smarty->assign('order_col', $order_col);
$smarty->assign('order_way', $order_way);
$smarty->display("vw_provenances.tpl");
