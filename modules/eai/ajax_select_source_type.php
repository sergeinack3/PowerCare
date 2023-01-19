<?php 
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CExchangeSource;

CCanDo::checkAdmin();

CView::checkin();

$all_name_sources = CExchangeSource::getAll();

$sources = array();
foreach ($all_name_sources as $_name_source) {
  $source = new $_name_source;
  $sources[$_name_source] = $source;
}

$smarty = new CSmartyDP();
$smarty->assign("sources", $sources);
$smarty->display("inc_all_name_sources.tpl");