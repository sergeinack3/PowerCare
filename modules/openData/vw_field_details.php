<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;

CCanDo::checkAdmin();

$page = CView::get('page', 'str notNull');

CView::checkin();

$group = CGroups::loadCurrent();

$file = new CFile();
$file->file_name = 'hospidiag_details.pdf';
$file->file_type = 'application/pdf';
$file->object_class = 'CGroups';
$file->object_id = $group->_id;

$file->loadMatchingObjectEsc();

if (!$file || !$file->_id) {
  $pdf = rtrim(CAppUI::conf('root_dir'), '/') . '/modules/openData/resources/hospidiag_details.pdf';
  if (!$pdf) {
    CAppUI::stepAjax('CFileReport-file_unfound-desc', UI_MSG_ERROR);
  }

  $file->fillFields();
  $file->setContent(file_get_contents($pdf));

  if ($msg = $file->store()) {
    CAppUI::stepAjax($msg, UI_MSG_ERROR);
  }
}

$smarty = new CSmartyDP();
$smarty->assign('file', $file);
$smarty->assign('page', explode('|', $page));
$smarty->display('vw_field_details.tpl');