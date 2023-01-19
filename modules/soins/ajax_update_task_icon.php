<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

CCanDo::checkRead();

$prescription_line_element_id = CView::get("prescription_line_element_id", "ref class|CPrescriptionLineElement");

CView::checkin();

$line = new CPrescriptionLineElement();
$line->load($prescription_line_element_id);

$line->loadRefTask();

$smarty = new CSmartyDP();
$smarty->assign("line", $line);
$smarty->assign("prescription", $line->_ref_prescription);
$smarty->display("inc_vw_task_icon");