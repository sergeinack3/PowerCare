<?php /** @noinspection PhpUndefinedClassInspection */

/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CModelObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;

CCanDo::checkAdmin();

$progress_id = CView::get('progress_id', 'ref class|CRefProgression notNull');
$start       = CView::get('start', 'num default|0');
$step        = CView::get('step', 'num default|100');

CView::checkin();

CView::enforceSlave();
$ref_progression = new CRefProgression();
$ref_progression->load($progress_id);

$ref_progression->loadRefRefCheck();
$ref_progression->loadBackRefs('errors', 'integrity_error_id ASC', "$start,$step");
$ref_progression->countBackRefs('errors');

/** @var CIntegrityError $_error */
foreach ($ref_progression->_back['errors'] as $_error) {
  $_class = $ref_progression->_ref_check->class;
  $_field = $ref_progression->_ref_check->field;
  /** @var CStoredObject $_obj */
  $_obj                                        = CModelObject::getInstance($_class);
  $_obj->$_field = $_error->missing_id;
  if (isset($_obj->_specs[$_field]->meta)) {
    $_obj->{$_obj->_specs[$_field]->meta} = $ref_progression->class;
  }

  $_error->_count_obj = $_obj->countMatchingListEsc();
}

$smarty = new CSmartyDP();
$smarty->assign('progression', $ref_progression);
$smarty->assign('start', $start);
$smarty->assign('step', $step);
$smarty->assign('errors', $ref_progression->_back['errors']);
$smarty->assign('total', $ref_progression->_count['errors']);
$smarty->display('inc_display_field_errors');