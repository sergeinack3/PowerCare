<?php
/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Ftp\CSourceFTP;

CCanDo::checkAdmin();

$file_name         = CValue::get('file_name');
$current_directory = CValue::get('current_directory');
$source_ftp_guid   = CValue::get('source_ftp_guid');

/** @var CSourceFTP $source */
$source = CMbObject::loadFromGuid($source_ftp_guid);
$source->getClient()->init($source);

$info = pathinfo($file_name);

$image = false;
$content = null;
switch ($extension = CMbArray::get($info, "extension")) {
  case 'txt':
  case 'hprim':
  case 'hl7':
  case 'ok':
  case 'HPR':
  case 'xml':
    $content = $source->getClient()->getData($current_directory . $file_name);
    break;

  case 'jpeg':
  case 'jpg':
  case 'gif':
  case 'png':
    $content1 = $source->getClient()->getData($current_directory . $file_name);
    $content  = base64_encode($content1);
    $image    = true;

  default:
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("content", $content);
$smarty->assign("image", $image);
$smarty->assign("extension", $extension);
$smarty->assign("file_name", $file_name);
$smarty->display("inc_show_file_ftp.tpl");


