<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClassHostField;

CCanDo::checkEdit();

$host_field_id = CView::get('host_field_id', 'ref class|CExClassHostField');

CView::checkin();

$host_field = new CExClassHostField();
$host_field->load($host_field_id);

$host_field->loadRefsNotes();

$smarty = new CSmartyDP();
$smarty->assign('host_field', $host_field);
$smarty->display('edit_host_field.tpl');