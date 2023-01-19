<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkRead();

$type = CView::get('type', 'enum list|import|export default|export');
$path = CView::get('path', 'str notNull');

CView::checkin();

if (!$path) {
  CAppUI::commonError("importTools-directory path is mandatory");
}

$file_path = "{$path}/{$type}.status";
if (!file_exists($file_path)) {
  CAppUI::commonError("importTools-status file doen not exists");
}

$status_lines = array();
$fp           = fopen($file_path, 'r');
$i            = 0;
while ($line = fgets($fp)) {
  $status_lines[$i]             = json_decode($line, true);
  $status_lines[$i]['duration'] = round($status_lines[$i]['duration'] / 60);
  if ($type == 'export') {
    $status_lines[$i]['increase_size'] = ($i > 0)
      ? $status_lines[$i]['size'] - $status_lines[$i - 1]['size'] : $status_lines[$i]['size'];
    $status_lines[$i]['size_per_pat']  = $status_lines[$i]['increase_size'] / $status_lines[$i]['patient_count'];
  }
  $i++;
}

$smarty = new CSmartyDP();
$smarty->assign('type', $type);
$smarty->assign('file_path', $file_path);
$smarty->assign('status_lines', $status_lines);
$smarty->display('vw_status_file');