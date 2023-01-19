<?php 
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CThumbnail;

CCanDo::checkAdmin();

CView::checkin();

$group = CGroups::loadCurrent();
$where = array(
  'file_name' => "= 'thumbnail_tester.pdf'"
);
$group->loadBackRefs('files', null, 1, null, null, null, null, $where);
if (!$group->_back['files']) {
  $content = file_get_contents(CAppUI::conf('root_dir') .'/modules/dPdeveloppement/images/thumbnail_tester.pdf');
  $file = new CFile();
  $file->file_name = 'thumbnail_tester.pdf';
  $file->setObject($group);
  $file->file_type = 'application/pdf';
  $file->fillFields();

  $file->loadMatchingObjectEsc();
  $file->updateFormFields();
  $file->setContent($content);

  $file->file_date = CMbDT::dateTime();

  if ($file && !$file->_id) {
    if ($msg = $file->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
  }
}
else {
  /** @var CFile $file */
  $file = reset($group->_back['files']);
}

$smarty = new CSmartyDP();
$smarty->assign('file', $file);
$smarty->assign('profiles', array_keys(CThumbnail::PROFILES));
$smarty->display('inc_rotation_thumbnail_tester.tpl');
