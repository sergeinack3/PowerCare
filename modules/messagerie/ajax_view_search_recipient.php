<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CJeeboxLDAPRecipient;

CCanDo::checkRead();

$field = CView::get('field', 'str');

CView::checkin();

$query = new CJeeboxLDAPRecipient();

$smarty = new CSmartyDP();
$smarty->assign('query', $query);
$smarty->assign('field', $field);
$smarty->assign('results', array());
$smarty->assign('type', 'PER');
$smarty->display('inc_search_recipient.tpl');