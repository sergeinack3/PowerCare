<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CModelObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Developpement\CRefCheckField;

CCanDo::checkAdmin();

$field_id = CView::get('field_id', 'ref class|CRefCheckField notNull');

CView::checkin();

CView::enforceSlave();

if (!$field_id) {
  CAppUI::commonError('CRefCheckField.none');
}

/** @var CRefCheckField $field_check */
$field_check = CModelObject::getInstance('CRefCheckField');
$field_check->load($field_id);
$field_check->loadFwdRef('ref_check_table_id', true);

$errors = $field_check->countErrorsByClass();

CMbArray::pluckSort($errors, SORT_DESC, 'count_errors');

$smarty = new CSmartyDP();
$smarty->assign('table', $field_check->_fwd['ref_check_table_id']);
$smarty->assign('field', $field_check);
$smarty->assign('errors', $errors);
$smarty->assign('meta', (count($errors) > 1));
$smarty->display('inc_display_errors');