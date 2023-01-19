<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;

CCanDo::check();

$data     = CView::post("data", "str");
$filename = CView::post("filename", "str");
$mimetype = CView::post("mimetype", "str default|application/force-download");

CView::checkin();

$data = stripslashes($data);

ob_clean();

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false);
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Content-Disposition: attachment; filename=\"".rawurlencode($filename)."\"");
header("Content-Type: $mimetype");
header("Content-Length: ".strlen($data));
header("Content-Transfer-Encoding: binary");

echo $data;

CApp::rip();