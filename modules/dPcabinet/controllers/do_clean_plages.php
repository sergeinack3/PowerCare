<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CPlageconsult;

CCanDo::checkAdmin();

$praticien_id = CValue::get("praticien_id");
$date         = CValue::get("date", CMbDT::date("+5 year"));
$limit        = CValue::get("limit", 100);

$plage = new CPlageconsult();
$plage->_spec->loggable = false;

$where = array();
if ($praticien_id) {
  $where["plageconsult.chir_id"] = "= '$praticien_id'";
}
$where["plageconsult.date"] = "> '$date'";

$count = $plage->countList($where);
CAppUI::setMsg("'$count' plages à supprimer après '$date'", UI_MSG_OK);

/** @var CPlageconsult[] $listPlages */
$listPlages = $plage->loadList($where, null, $limit);

foreach ($listPlages as $_plage) {
  if ($msg = $_plage->delete()) {
    CAppUI::setMsg("Plage non supprimée", UI_MSG_ERROR);
  }
  else {
    CAppUI::setMsg("Plage supprimée", UI_MSG_OK);
  }
}

echo CAppUI::getMsg();