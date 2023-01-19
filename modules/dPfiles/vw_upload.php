<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CUploader;

CCanDo::checkAdmin();

$uploader = new CUploader();

$files          = glob(CFile::getDirectory() . "/upload/*");
$uploaded_files = array();

foreach ($files as $_file) {
  if (!is_file($_file)) {
    continue;
  }

  $uploaded_files[] = array(
    "name" => basename($_file),
    "path" => $_file,
    "size" => filesize($_file),
    "date" => CMbDT::strftime(CMbDT::ISO_DATETIME, filemtime($_file)),
  );
}

$temp_uploads  = glob(CFile::getDirectory() . "/upload/temp/*/*.part*");
$uploaded_temp = array();

foreach ($temp_uploads as $_temp) {
  if (!is_file($_temp)) {
    continue;
  }

  $_dir = basename(dirname($_temp));

  if (!isset($uploaded_temp[$_dir])) {
    $uploaded_temp[$_dir] = array(
      "files" => 0,
      "name"  => $_dir,
      "date"  => CMbDT::strftime(CMbDT::ISO_DATETIME, filemtime(dirname($_temp))),
    );
  }

  $uploaded_temp[$_dir]["files"]++;
}


$smarty = new CSmartyDP();
$smarty->assign("uploader", $uploader);
$smarty->assign("uploaded_files", $uploaded_files);
$smarty->assign("uploaded_temp", $uploaded_temp);
$smarty->display("vw_upload.tpl");
