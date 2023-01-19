<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCando;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Admin\CKerberosLdapIdentifier;
use Ox\Mediboard\Admin\CUser;

CCanDo::checkAdmin();

$uid = CView::post('file_uid', 'str');

CView::checkin();

set_time_limit(600);

$uid  = preg_replace('/[^\d]/', '', $uid);
$temp = CAppUI::getTmpPath('kerberos_import');
$file = "{$temp}/{$uid}";

$csv = new CCSVFile($file, CCSVFile::PROFILE_AUTO);
$csv->setColumnNames(['mediboard_identifier', 'domain_identifier']);

while ($line = $csv->readLine(true, true)) {
  $user_to_bind = new CUser();
  $user_to_bind->user_username = $line['mediboard_identifier'];

  if (!$user_to_bind->loadMatchingObjectEsc()) {
    CAppUI::setMsg("common-error-Unable to find user: '%s'", UI_MSG_ERROR, $line['mediboard_identifier']);
    continue;
  }

  $identifier           = new CKerberosLdapIdentifier();
  $identifier->user_id  = $user_to_bind->_id;
  $identifier->username = $line['domain_identifier'];

  if (!$identifier->loadMatchingObjectEsc()) {
    if ($msg = $identifier->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
      continue;
    }

    CAppUI::setMsg('CKerberosLdapIdentifier-msg-created', UI_MSG_OK);
  } else {
    CAppUI::setMsg('CKerberosLdapIdentifier-msg-Object found', UI_MSG_OK);
  }
}

echo CAppUI::getMsg();
CApp::rip();