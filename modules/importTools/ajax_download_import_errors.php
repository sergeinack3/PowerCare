<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;


CCanDo::checkRead();

$file = CView::get('file', 'str notNull');

CView::checkin();

if (!$file) {
  CApp::rip();
}

$file = str_replace("import.status", "import.log", $file);

if (!file_exists($file)) {
  CAppUI::commonError("importTools-root_path.none");
}

// Direct download of the file
// BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
// [http://bugs.php.net/bug.php?id=16173]
header("Pragma: ");
header("Cache-Control: ");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
// END extra headers to resolve IE caching bug

header("MIME-Version: 1.0");

header("Content-disposition: attachment; filename=\"import_errors.txt\";");
header("Content-type: text/plain");
header("Content-length: " . filesize($file));

readfile($file);