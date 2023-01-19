<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Developpement\CLogParser;
use Ox\Mediboard\Developpement\CRedisLogParser;

CCanDo::checkAdmin();

$log_type     = CView::post('log_type', "enum list|" . implode('|', CLogParser::$log_types));
$file         = CValue::files('formfile');
$file_path    = CView::post('log_file_path', 'str');
$hits_per_sec = CView::post('hits_per_sec', 'bool default|0');
$show_size    = CView::post('show_size', 'bool default|0');

CView::checkin();

if (!in_array($log_type, CLogParser::$log_types)) {
  CAppUI::stepAjax('CLogParser-type-not-handled', UI_MSG_ERROR);
}

$filename = '';
if ($file && $file['tmp_name']) {
  $dir      = rtrim(CAppUI::conf('root_dir'), '/\\') . '/tmp';
  $filename = "$dir/{$file['name'][0]}";

  move_uploaded_file($file['tmp_name'][0], $filename);
}
elseif ($file_path && file_exists($file_path)) {
  $filename = str_replace('\\\\', '/', $file_path);
}
else {
  CAppUI::stepAjax('CFile-not-exists', UI_MSG_ERROR, $file_path);
}

switch ($log_type) {
  case 'redis':
    $start = microtime(true);

    $parser = new CRedisLogParser();
    $remove = ($file_path) ? false : true;
    $result = $parser->parseFile($filename, $remove, $show_size);

    $calls = array();
    foreach ($result as $_key => $_infos) {
      foreach (array_keys($_infos) as $_call_type => $_value) {
        if ($_value != 'children' && !in_array($_value, $calls)) {
          $calls[] = $_value;
        }
      }
    }

    $nb_lines = $parser->getNbLines();
    $end = microtime(true);

    $smarty = new CSmartyDP();
    $smarty->assign('result', $result);
    $smarty->assign('size_mode', $show_size);
    $smarty->assign('calls', $calls);
    $smarty->assign('hits_per_sec', $hits_per_sec);
    $smarty->assign('date_min', gmdate("d/m/Y H:i:s", $parser->getMinTime()));
    $smarty->assign('date_max', gmdate("d/m/Y H:i:s", $parser->getMaxTime()));
    $smarty->assign('duration', $parser->getMaxTime() - $parser->getMinTime());
    $smarty->assign('parsing_time', $end - $start);
    $smarty->assign('nb_lines', $nb_lines);
    $smarty->assign('file_name', $parser->getFileName());
    $smarty->display('inc_redis_log_parser');
    break;
  default:
    CAppUI::stepAjax('CLogParser-type-not-handled', UI_MSG_ERROR);
}