<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Interop\Ftp\CSourceSFTP;

CCanDo::checkAdmin();

$exchange_source_guid = CView::get("exchange_source_guid", "str");
$filename             = CView::get("filename"            , "str");
CView::checkin();

/** @var CSourceSFTP|CSourceSFTP $exchange_source */
$exchange_source = CMbObject::loadFromGuid($exchange_source_guid);
$data = $exchange_source->getClient()->getData($filename);

header("Content-Disposition: attachment; filename=".urlencode(basename($filename)));
header("Content-Type: text/plain; charset=".CApp::$encoding);
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: post-check=0, pre-check=0", false );
header("Content-Length: ".strlen($data));
echo $data;
