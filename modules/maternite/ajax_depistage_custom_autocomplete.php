<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CDepistageGrossesseCustom;

CCanDo::checkRead();
$keyword = CView::get("_libelle_customs", "str");
CView::checkin();

$ds                = CSQLDataSource::get("std");
$where             = array();
$depistage_customs = new CDepistageGrossesseCustom();
$order             = "libelle ASC";
$group_by          = "libelle";

if (!empty($keyword)) {
  $where['libelle'] = $ds->prepareLike("%" . reset($keyword) . "%");
}

$fields_customs = $depistage_customs->loadList($where, $order, null, $group_by);

$smarty = new CSmartyDP();
$smarty->assign("fields_customs", $fields_customs);
$smarty->display("inc_depistage_custom.tpl");
