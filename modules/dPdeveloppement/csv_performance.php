<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CHTTPClient;
use Ox\Core\CMbArray;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Core\Sessions\CSessionHandler;

CCanDo::checkRead();

CSessionHandler::writeClose();

$url = "http://localhost/mediboard/index.php?m=system&a=performance";

$memory = array();
$profiling_keys = array(
  'curl_total_time',
  'profiling_total_time',
  'framework_init_time',
  'session_time',
  'framework_time',
  'app_time',
  'db_count',
  'framework_init_mem',
  'session_mem',
  'framework_mem',
  'app_mem',
  'autoload_count'
);
$profiling = array_fill_keys($profiling_keys, array());

// Send warmup request
$request = new CHTTPClient($url);
$request->setOption(CURLOPT_RETURNTRANSFER, 1);
$request->setOption(CURLOPT_HEADER, 1);
$request->setOption(CURLOPT_COOKIE, ' mediboard='.session_id().'; mediboard-profiling=%221%22;');
$result = $request->get();

$i = 0;
while ($i++ < 10) {
  // Init and send request
  $request = new CHTTPClient($url);
  $request->setOption(CURLOPT_RETURNTRANSFER, 1);
  $request->setOption(CURLOPT_HEADER, 1);
  $request->setOption(CURLOPT_COOKIE, ' mediboard='.session_id().'; mediboard-profiling=%221%22;');
  $result = $request->get(false);

  // Fetch result info
  $profiling['curl_total_time'][] = $request->getInfo(CURLINFO_TOTAL_TIME) * 1000;
  preg_match('/(X-Mb-Timing: )(.*)/', $result, $matches);
  $performance = json_decode($matches[2], true);
  $profiling['profiling_total_time'][] = $performance['end'] - $performance['start'];
  $profiling['db_count'][] = $performance['dbcount'];
  $framework_time = 0;
  $framework_memory = array();

  // Parse profiling cookie
  foreach ($performance['steps'] as $_step) {
    switch ($_step['label']) {
      case 'init':
        $profiling['framework_init_time'][] = $_step['dur'];
        $profiling['framework_init_mem'][] = $_step['mem'];
        break;
      case 'session':
        $profiling['session_time'][] = $_step['dur'];
        $profiling['session_mem'][] = $_step['mem'];
        break;
      case 'app':
        $profiling['app_time'][] = $_step['dur'];
        $profiling['app_mem'][] = $_step['mem'];
        break;
      default:
        $framework_time += $_step['dur'];
        $framework_memory[] = $_step['mem'];
        break;
    }
  }
  $profiling['framework_time'][] = $framework_time;
  $profiling['framework_mem'][] = max($framework_memory);

  $request->closeConnection();
  sleep(1);
}

foreach ($profiling as $_type => $_value) {
  if ($_type != 'autoload_count') {
    $profiling[$_type] = CMbArray::average($profiling[$_type]);
  }
}

$profiling['autoload_count'] = CApp::$performance['autoloadCount'];

$csv = new CCSVFile(null, CCSVFile::PROFILE_OPENOFFICE);
$csv->writeLine($profiling_keys);
$csv->writeLine($profiling);

$csv->stream("performance");

CApp::rip();
