<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Developpement\CRedisLogParser;

CCanDo::checkAdmin();

$key       = CView::get('key', 'str notNull');
$file_name = CView::get('file_name', 'str notNull');

CView::checkin();

$parser = new CRedisLogParser();
$occurences = $parser->searchOccurences($file_name, $key);

$smarty = new CSmartyDP();
$smarty->assign('key', $key);
$smarty->assign('occurences', $occurences);
$smarty->display('vw_redis_logs_details');
