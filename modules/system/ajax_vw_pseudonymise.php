<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\System\CObjectPseudonymiser;

CCanDo::checkAdmin();

$ds_hd = CSQLDataSource::get('hospi_diag', true);

if (!$ds_hd) {
  unset(CObjectPseudonymiser::$classes_handled['CHDEtablissement']);
}
else {
  if (!$ds_hd->hasTable('hd_etablissement')) {
    unset(CObjectPseudonymiser::$classes_handled['CHDEtablissement']);
  }
}

$classes = array_keys(CObjectPseudonymiser::$classes_handled);

$class_selected = CView::get('class_selected', 'enum list|' . implode('|', $classes));
$delais         = CView::get('delais', 'num default|0');

CView::checkin();

$class_requested = true;

if (!$class_selected) {
  $class_selected  = 'CPatient';
  $class_requested = false;
}

/** @var CMbObject $obj */
$obj   = new $class_selected();
$total = $obj->countList();

$cache   = new Cache('pseudonymise', $class_selected, Cache::OUTER | Cache::DISTR);
$last_id = ($cache->get()) ?: null;

$smarty = new CSmartyDP();
$smarty->assign('classes', $classes);
$smarty->assign('class_selected', $class_selected);
$smarty->assign('total', $total);
$smarty->assign('class_requested', $class_requested);
$smarty->assign('last_id', $last_id);
$smarty->assign('counts', CObjectPseudonymiser::$counts);
$smarty->assign('delais', $delais);
$smarty->display('pseudonymise/inc_vw_pseudonymise');