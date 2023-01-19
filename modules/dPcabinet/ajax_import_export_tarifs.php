<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$action = CView::get('action', 'enum list|import|export notNull');

CView::checkin();

$smarty = new CSmartyDP();
$smarty->assign('tarif', new CTarif());
$smarty->assign('groups', CGroups::loadGroups());
$smarty->assign('action', $action);
$smarty->display('inc_import_export_tarifs.tpl');