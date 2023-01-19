<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Maternite\CSuiviGrossesse;

CCanDo::checkEdit();

$motif              = CView::post("motif", "str");
$rques              = CView::post("rques", "str");
$suivi_grossesse_id = CView::post("suivi_grossesse_id", "ref class|CSuiviGrossesse");
$consultation_id    = CView::post("consultation_id", "ref class|CConsultation");

$suivi_grossesse = CSuiviGrossesse::findOrNew($suivi_grossesse_id);
$suivi_grossesse->bind($_POST);

CView::checkin();

if ($msg = $suivi_grossesse->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}
else {
  CAppUI::setMsg(CAppUI::tr("CSuiviGrossesse-msg-modify"));
}

$consultation        = CConsultation::findOrFail($consultation_id);
$consultation->motif = stripslashes($motif);
$consultation->rques = stripslashes($rques);
if ($msg = $consultation->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}

echo CAppUI::getMsg();
