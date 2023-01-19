<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\CSourceSFTP;

CCanDo::checkAdmin();

$source_guid       = CView::post("source_guid"      , "str");
$current_directory = CView::post("current_directory", "str");
$files             = CValue::read($_FILES, "import");
CView::checkin();

$message = array(
  "result" => "Ajout du fichier",
  "resultNumber" => 0,
  "error" => array(),
);

/** @var CSourceFTP|CSourceSFTP $source */
$source = CMbObject::loadFromGuid($source_guid);
foreach ($files["name"] as $index => $_file) {
  if (!$_file) {
    continue;
  }

  $remote_file = $_file;
  $sent_file   = $files["tmp_name"][$index];

  if ($source instanceof CSourceSFTP) {

    $sent_file   = $_file;
    $remote_file = $files["tmp_name"][$index];
  }

  try {
      $source->_destination_file = $current_directory;
      $client                    = $source->getClient();
     $client->addFile($sent_file, $remote_file);

      $message["resultNumber"]++;
  }
  catch(CMbException $e) {

    $message["error"][] = CAppUI::tr("CExhangeFile-error", $_file, $client->getError());
  }
}

CAppUI::callbackAjax("window.parent.ExchangeSource.closeAfterSubmit", $message);

CApp::rip();
