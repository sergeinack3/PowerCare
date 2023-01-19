<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id  = CView::post("sejour_id", "ref class|CSejour");
$box_id     = CView::post("box_id", "ref class|CLit");
$service_id = CView::post("service_id", "ref class|CService");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

$affectation = new CAffectation();
$affectation->sejour_id = $sejour_id;
$affectation->lit_id = $box_id;
$affectation->service_id = $service_id;
$affectation->entree = CMbDT::dateTime();
$affectation->_mutation_urg = true;

$retour = $sejour->forceAffectation($affectation);

$msg = is_string($retour) ? $retour : null;

CAppUI::setMsg($msg ? : "CAffectation-msg-create", $msg ? UI_MSG_ERROR : UI_MSG_OK);

echo CAppUI::getMsg();
