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

$source_id = CView::get('source_id', 'ref class|CSourceLDAP');

CView::checkin();

$source = new CSourceLDAP();

if ($source_id) {
  $source->load($source_id);

  if (!$source || !$source->_id) {
    CAppUI::commonError();
  }
}

$source->needsEdit();
$source->loadRefsNotes();
$source->updateGroupsSpecs();
$source->loadRefSourceLDAPLinks();

$smarty = new CSmartyDP();
$smarty->assign('source', $source);
$smarty->display('edit_ldap_source');