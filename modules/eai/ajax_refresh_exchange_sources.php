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
use Ox\Mediboard\System\CSourcePOP;

CCanDo::check();

$source_class = CView::get("source_class", "str");
CView::checkin();

$class = new $source_class;

$sources = $class->loadList();
foreach ($sources as $_source) {
  if ($_source instanceof CSourcePOP) {
    $_source->loadRefMetaObject();
  }
}

$smarty = new CSmartyDP();
$smarty->assign("_sources", $sources);
$smarty->assign("name"    , $source_class);
$smarty->display("inc_vw_sources.tpl");