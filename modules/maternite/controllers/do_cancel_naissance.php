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
use Ox\Mediboard\Maternite\CNaissance;

CCanDo::checkEdit();

$naissance_id = CView::post("naissance_id", "ref class|CNaissance");

CView::checkin();

$naissance = new CNaissance();
$naissance->load($naissance_id);

$sejour_enfant                = $naissance->loadRefSejourEnfant();
$sejour_enfant->entree_reelle = "";
$sejour_enfant->annule        = 1;

$msg = $sejour_enfant->store();

CAppUI::setMsg($msg ?: CAppUI::tr("CSejour-msg-modify"), $msg ? UI_MSG_ERROR : UI_MSG_OK);

$msg = $naissance->delete();

CAppUI::setMsg($msg ?: CAppUI::tr("CNaissance-msg-delete"), $msg ? UI_MSG_ERROR : UI_MSG_OK);

echo CAppUI::getMsg();