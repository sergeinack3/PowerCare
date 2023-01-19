<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CSourceLDAP;
use Ox\Mediboard\Admin\CSourceLDAPLink;

CCanDo::checkAdmin();

$source_ldap_id  = CView::post('source_ldap_id', 'num');
$source_password = CView::post('password', 'str');
$del             = CView::post('del', 'bool default|0');

CView::checkin();

$source = new CSourceLDAP();

if ($del) {
  $source->load($source_ldap_id);

  if ($source && $source->_id) {
    if ($msg = $source->delete()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg("{$source->_class}-msg-delete", UI_MSG_OK);
    }
  }

  echo CAppUI::getMsg();
  CApp::rip();
}

$source->bind($_POST);

$_groups = $source->_groups;

if (!$source_password) {
  $source->password = null;
}

if ($msg = $source->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}
else {
  CAppUI::setMsg(($source_ldap_id) ? "{$source->_class}-msg-modify" : "{$source->_class}-msg-create", UI_MSG_OK);
}

$link  = new CSourceLDAPLink();
$links = $source->loadRefSourceLDAPLinks();

// No group checked, we assume that all groups will be enabled
if (!$_groups && $links) {
  $link->deleteAll(CMbArray::pluck($links, '_id'));
}

if ($_groups && $source && $source->_id) {
  $_groups = explode('|', $_groups);

  // Deleting links already stored, but not still checked
  foreach ($links as $_link) {
    if (!in_array($_link->group_id, $_groups)) {
      $_link->delete();
    }
  }

  // Storing new links, keeping already existent ones
  foreach ($_groups as $_group_id) {
    $_link                 = new CSourceLDAPLink();
    $_link->source_ldap_id = $source->_id;
    $_link->group_id       = $_group_id;

    if (!$_link->loadMatchingObjectEsc()) {
      $_link->store();
    }
  }
}

echo CAppUI::getMsg();
CApp::rip();