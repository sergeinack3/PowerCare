<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbPath;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimedPicture;

CCanDo::checkAdmin();
$path             = str_replace("..", "", CValue::post("path"));
$timed_picture_id = CView::post("timed_picture_id", "ref class|CSupervisionTimedPicture");
CView::checkin();

if (is_file($path) && strpos(CSupervisionTimedPicture::PICTURES_ROOT, $path) !== 0) {
  $timed_picture = new CSupervisionTimedPicture();
  $timed_picture->load($timed_picture_id);

  $file = new CFile();
  $file->setObject($timed_picture);
  $file->fillFields();
  $file->file_name = basename($path);
  $file->doc_size  = filesize($path);
  $file->file_type = CMbPath::guessMimeType($path);
  $file->setCopyFrom($path);

  if ($msg = $file->store()) {
    CAppUI::setMsg($msg, UI_MSG_WARNING);
  }
  else {
    CAppUI::setMsg("Image enregistrée");
  }
}

echo CAppUI::getMsg();
CApp::rip();