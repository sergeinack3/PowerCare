<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CKerberosLdapIdentifier;
use Ox\Mediboard\Admin\CUser;

CCanDo::checkAdmin();

$identifier_id = CView::get('identifier_id', 'ref class|CKerberosLdapIdentifier');
$user_id = CView::get('user_id', 'ref class|CUser');

CView::checkin();

$identifier = new CKerberosLdapIdentifier();
$identifier->load($identifier_id);
$identifier->needsEdit();

if (!$identifier->_id) {
  $identifier->user_id = CUser::findOrFail($user_id)->_id;
}

$identifier->loadRefsNotes();

$smarty = new CSmartyDP();
$smarty->assign('identifier', $identifier);
$smarty->display('edit_kerberos_ldap_identifier');