<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CSourceLDAP;

CCanDo::checkAdmin();

$source_id = CView::get('source_id', 'ref class|CSourceLDAP notNull');

CView::checkin();

if (!$source_id) {
  CAppUI::commonError();
}

$source = new CSourceLDAP();
$source->load($source_id);

if (!$source->_id) {
  CAppUI::commonError();
}

$smarty = new CSmartyDP();
$smarty->assign('source', $source);
$smarty->display('test_ldap_source');