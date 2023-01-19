<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\OpenData\CCommuneImport;

CCanDo::checkAdmin();

CView::checkin();

$import_france = array_keys(CCommuneImport::$versions_france);

$smarty = new CSmartyDP();
$smarty->assign('versions_france', $import_france);
$smarty->display('vw_import_communes.tpl');