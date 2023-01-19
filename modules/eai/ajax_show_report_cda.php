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
use Ox\Interop\Cda\CExchangeCDA;
use Ox\Interop\Eai\CReport;

CCanDo::checkEdit();

$exchange_cda_id = CView::get('exchange_cda_id', 'ref class|CExchangeCDA');
CView::checkin();

$exchange_cda = new CExchangeCDA();
$exchange_cda->load($exchange_cda_id);

$report = CReport::toObject($exchange_cda->report);

$smarty = new CSmartyDP("modules/eai");
$smarty->assign('report', $report);
$smarty->display('report/inc_report.tpl');
