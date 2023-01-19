<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;

CCanDo::checkEdit();

$consultation_ids = CView::get("consultation_ids", "str");

CView::checkin();

$msg = array();
foreach ($consultation_ids as $_consultation_id) {
  $consultation = new CConsultation();
  $consultation->load($_consultation_id);

  // Fermeture de cotation
  $consultation->valide = 1;

  if ($_msg = $consultation->store()) {
    $msg[] = $_msg;
  }
}
if (count($msg) > 0) {
  CAppUI::stepAjax(implode(",", $msg), UI_MSG_ERROR);
}

$smarty = new CSmartyDP();

$smarty->display("tdb_cotation/tdb_cotation_multiple_cloture");
