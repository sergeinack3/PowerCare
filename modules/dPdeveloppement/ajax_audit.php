<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Developpement\DataAuditor;

CCanDo::checkAdmin();

$host1      = CView::post('host1', 'str notNull');
$host2      = CView::post('host2', 'str notNull');
$start_date = CView::post('start_date', 'dateTime notNull');
$end_date   = CView::post('end_date', 'dateTime notNull');

CView::checkin();

CApp::setMemoryLimit('64M');

try {
    $audit = new DataAuditor($host1, $host2, $start_date, $end_date);
    $audit->run();
} catch (Exception $e) {
    CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
}

$smarty = new CSmartyDP();
$smarty->assign('audit', $audit);
$smarty->display('vw_audit_result');
