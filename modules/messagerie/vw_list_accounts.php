<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CSourcePOP;

CCanDo::checkAdmin();

CView::checkin();

$source = new CSourcePOP();
/** @var CSourcePOP[] $sources */
$sources = $source->loadList();

foreach ($sources as $_source) {
  $_source->loadRefMetaObject();
  $_source->countRefMails();
}

//smarty
$smarty = new CSmartyDP();
$smarty->assign("sources", $sources);
$smarty->display("vw_list_accounts.tpl");