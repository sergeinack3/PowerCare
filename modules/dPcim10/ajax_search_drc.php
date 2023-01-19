<?php
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cim10\Drc\CDRCConsultationResult;

CCanDo::checkRead();

$keywords         = CView::get('keywords', 'str');
$result_class_id  = CView::get('result_class_id', 'num');
$age              = CView::get('age', 'num min|0 max|150');
$sex              = CView::get('sex', 'enum list|1|2|3');

CView::checkin();

$results = CDRCConsultationResult::search($keywords, $sex, $age, $result_class_id);

$smarty = new CSmartyDP();
$smarty->assign('results', $results);
$smarty->display('drc/list_drc.tpl');