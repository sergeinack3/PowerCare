<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Mediboard\System\Forms\CExClassEvent;

CCanDo::checkAdmin();

$nb_lines       = 0;
$old_name_moins = "appel_j_moins_1";
$old_name_plus  = "appel_j_plus_1";

$new_name_moins = "appel_j_moins_1_auto";
$new_name_plus  = "appel_j_plus_1_auto";

$where   = array();
$where[] = "event_name = '$old_name_moins' OR event_name = '$old_name_plus'";

$ex_class_event  = new CExClassEvent();
$ex_class_events = $ex_class_event->loadList($where);

foreach ($ex_class_events as $_event) {
  $_event->event_name = ($_event->event_name == $old_name_moins) ? $new_name_moins : $new_name_plus;

  $_event->store();

  $nb_lines++;
}

CAppUI::stepAjax(CAppUI::tr("CAppelSejour-msg-%s modified lines", $nb_lines), UI_MSG_OK);
