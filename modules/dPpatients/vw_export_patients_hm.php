<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

CView::checkin();

$ds             = CSQLDataSource::get('std');
$query          = 'SELECT COUNT(*) from `patients`;';
$total_patients = $ds->loadResult($query);

$group  = CGroups::loadCurrent();
$finess = $group->finess;

$file_path = rtrim(CAppUI::conf('root_dir'), '/\\') . '/tmp/export-hm-' . $group->text;

$file_exists = file_exists($file_path);

$smarty = new CSmartyDP();
$smarty->assign('total_patients', $total_patients);
$smarty->assign('finess', $finess);
$smarty->assign('file_exists', $file_exists);
$smarty->assign('group', $group);
$smarty->display('vw_export_patients_hm.tpl');