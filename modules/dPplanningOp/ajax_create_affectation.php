<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");
$lit_id    = CView::get("lit_id", "ref class|CLit");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$affectation = new CAffectation();
$affectation->sejour_id = $sejour_id;
$affectation->lit_id = $lit_id;
$affectation->entree = $sejour->entree;
$affectation->sortie = $sejour->sortie;

$msg = $affectation->store();

CAppUI::setMsg($msg ? : CAppUI::tr("CAffectation-msg-create"), $msg ? UI_MSG_ERROR : UI_MSG_OK);

echo CAppUI::getMsg();