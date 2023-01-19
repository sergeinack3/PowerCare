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
use Ox\Mediboard\Cim10\Drc\CDRC;
use Ox\Mediboard\Cim10\Drc\CDRCConsultationResult;

CCanDo::checkRead();

$result_id    = CView::get('result_id', 'num');
$information  = CView::get('information', 'enum list|siblings|criteria|positions|follow_up|diagnoses|transcodings|details notNull');

CView::checkin();

$result = CDRCConsultationResult::get($result_id, CDRC::LOAD_FULL);

$smarty = new CSmartyDP();
$smarty->assign('result', $result);

switch ($information) {
  case 'criteria':
    $template = 'list_criteria';
    break;
  case 'details':
    $result->details = nl2br($result->details);
    $template = 'details';
    break;
  case 'diagnoses':
    $template = 'critical_diagnoses';
    break;
  case 'follow_up':
    $template = 'follow_up';
    break;
  case 'positions':
    $template = 'list_positions';
    break;
  case 'siblings':
    $template = 'list_siblings';
    break;
  case 'transcodings':
    $template = 'transcodings';
    break;
  default:
}

$smarty->display("drc/{$template}.tpl");
