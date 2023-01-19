<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CPlageconsult;

CCanDo::checkEdit();

$plage_id           = CValue::get("plage_id");
$repeat             = min(CValue::get("_repeat", 0), 100);
$repeat_mode        = CValue::get("_type_repeat");
$_update_pauses      = CValue::get("_update_pauses");
$_pauses             = str_replace('\\', '', CValue::get("_pauses"));

// because of current plage
$nb_extend = $repeat++;

//load plage
$plage_consult = new CPlageconsult();
$plage_consult->load($plage_id);
$plage_consult->_type_repeat = $repeat_mode;
$plage_consult->_update_pauses = $_update_pauses;
$plage_consult->_pauses = $_pauses;

//behaviour
$skipped = 0;
$created = 0;
$failed = 0;

$pause = 0;

//do the repeat work (2 = startng next week)
while (1 <= $nb_extend) {
  //switch to next direct, to avoid current plage
  $nb_extend -= $plage_consult->becomeNext();

  //plage doesn't exist

  if (!$plage_consult->_id) {
    if ($msg = $plage_consult->store()) {
      $failed++;
    }
    else {
      $created++;
    }
  }
  //exist = skipped
  else {
    if ($_update_pauses) {
      if ($msg = $plage_consult->store()) {
        $pause++;
      }
    }
    $skipped++;
  }
}

CAppUI::setMsg("Cplageconsult-msg-extend_finished_nb%d_week", UI_MSG_OK, $repeat-1);
if ($created) {
  CAppUI::setMsg('Cplageconsult-msg-creation_created_nb%d', UI_MSG_OK, $created);
}

if ($skipped) {
  CAppUI::setMsg('Cplageconsult-msg-creation_skipped_nb%d', UI_MSG_ALERT, $skipped);
}

if ($failed) {
  CAppUI::setMsg('Cplageconsult-msg-creation_failed_nb%d', UI_MSG_ERROR, $failed);
}

if ($pause) {
  CAppUI::setMsg('Cplageconsult_pause-msg-creation_failed_nb%d', UI_MSG_ERROR, $pause);
}

echo CAppUI::getMsg();