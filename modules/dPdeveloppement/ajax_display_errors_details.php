<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Developpement\CRefCheckField;

CCanDo::checkAdmin();

$field_id  = CView::get('field_id', 'ref class|CRefCheckField notNull');
$start     = CView::get('start', 'num default|0');
$step      = CView::get('step', 'num default|30');
$container = CView::get('container', 'str');

CView::checkin();

CView::enforceSlave();

if (!$field_id) {
  CAppUI::commonError('CRefCheckField.none');
}

$ref_field = new CRefCheckField();
$ref_field->load($field_id);

if (!$ref_field->target_class) {
  $ds = $ref_field->getDS();

  $where = array(
    'target_class' => $ds->prepare('= ?', $container)
  );

  /** @var CRefCheckField $back_field */
  $back_field = $ref_field->loadBackRefs('target_classes', null, 1, null, null, null, null, $where);
  $back_field = reset($back_field);
}
else {
  $back_field = $ref_field;
}

$errors = $back_field->loadBackRefs('errors', 'count_use DESC, missing_id ASC', "$start,$step");
$total = $back_field->countBackRefs('errors');

$smarty = new CSmartyDP();
$smarty->assign('field', $ref_field);
$smarty->assign('start', $start);
$smarty->assign('step', $step);
$smarty->assign('total', $total);
$smarty->assign('errors', $errors);
$smarty->assign('container', $container);
$smarty->display('inc_display_errors_details');