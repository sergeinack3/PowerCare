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
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;

CCanDo::checkRead();

$consult_id = CView::post("consult_id", "ref class|CConsultation");
$sejour_id  = CView::post("sejour_id", "ref class|CSejour");

CView::checkin();

$consult = new CConsultation();
$consult->load($consult_id);

if ($consult->sejour_id) {
  $sejour = $consult->loadRefSejour();

  $sejour->sortie_reelle = "now";

  $use_custom_mode_entree = CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree");

  $modes_entree = CModeEntreeSejour::listModeEntree($sejour->group_id);

  if ($use_custom_mode_entree && count($modes_entree)) {
    foreach ($modes_entree as $_mode_entree) {
      if ($_mode_entree->code == "8") {
        $sejour->mode_entree_id = $_mode_entree->_id;
        break;
      }
    }
  }
  else {
    $sejour->mode_entree = "8";
  }

  $use_custom_mode_sortie = CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie");

  $modes_sortie = CModeSortieSejour::listModeSortie($sejour->group_id);

  if ($use_custom_mode_sortie && count($modes_sortie)) {
    foreach ($modes_sortie as $_mode_sortie) {
      if ($_mode_sortie->code == "8") {
        $sejour->mode_sortie_id = $_mode_sortie->_id;
        break;
      }
    }
  }
  else {
    $sejour->mode_sortie = "normal";
  }

  $msg = $sejour->store();

  CAppUI::setMsg($msg ?: CAppUI::tr("CSejour-msg-modify"), $msg ? UI_MSG_ALERT : UI_MSG_OK);
}

echo CAppUI::getMsg();