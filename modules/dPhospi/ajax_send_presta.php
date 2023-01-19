<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

// Si le destinataire tiers pour cet établissement n'est pas softway
if (CAppUI::gconf("dPhospi prestations systeme_prestations_tiers") == "Aucun") {
  return;
}

$sejours_ids = CView::get("sejours_ids", "str");

CView::checkin();

if (!$sejours_ids) {
  CAppUI::setMsg("CSejour.none", UI_MSG_WARNING);
  echo CAppUI::getMsg();

  return;
}

$sejours_ids = explode(",", $sejours_ids);

try {
  $sejour = new CSejour();
  foreach ($sejour->loadList(array("sejour_id" => CSQLDataSource::prepareIn($sejours_ids))) as $_sejour) {
    $flow = CPrestation::generateFlow($_sejour);

    if (!$flow) {
      CAppUI::setMsg("CPrestation-msg-flux vide", UI_MSG_WARNING);
      continue;
    }

    CPrestation::send($_sejour, $flow);

    CAppUI::setMsg("CPrestation-msg-sent");
  }
}
catch (CMbException $e) {
  CAppUI::setMsg("CPrestation-msg-sent-fail", UI_MSG_ERROR, $e->getMessage());
}

echo CAppUI::getMsg();