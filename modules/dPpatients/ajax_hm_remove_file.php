<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$group = CGroups::loadCurrent();

$file_name = 'export-hm-' . $group->text;
$file_path = rtrim(CAppUI::conf('root_dir'), '/\\') . '/tmp/' . $file_name;

if (file_exists($file_path)) {
  $delete = unlink($file_path);

  if ($delete) {
    CAppUI::stepAjax('CFile-msg-delete', UI_MSG_OK);
  }
  else {
    CAppUI::stepAjax('dPpatients-export-hm-error-unlink', UI_MSG_ERROR);
  }
}