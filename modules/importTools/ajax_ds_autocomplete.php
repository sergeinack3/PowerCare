<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Import\ImportTools\CImportTools;


CCanDo::checkAdmin();

$dsn = CView::get("dsn", "str", true);

CView::checkin();

$databases = CImportTools::getAllDatabaseInfo();

foreach ($databases as $_dsn => &$_info) {
  $_info = CImportTools::getDatabaseStructure($_dsn, false, true);
}

$smarty = new CSmartyDP();
$smarty->assign("dsn", $dsn);
$smarty->assign("databases", $databases);
$smarty->display("inc_ds_autocomplete.tpl");