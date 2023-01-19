<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;

$prat_id = CValue::post("prat_id");
$dialog  = CValue::post("dialog");

if (!isset($current_m)) {
    $current_m = CValue::post("current_m", "dPcabinet");
}

$consult = new CConsultation();
$consult->load(CValue::post("consultation_id"));
$consult->loadRefPlageConsult();

try {
    $consult->changePraticien($prat_id);
} catch (CMbException $e) {
    CAppUI::setMsg($e->getMessage(), UI_MSG_ERROR);
}

if ($msg = $consult->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
}

if ($current_m == "dPurgences") {
    CAppUI::redirect("m=dPurgences&tab=edit_consultation&selConsult=$consult->_id&ajax=$ajax");
} else {
    if (!$dialog) {
        CAppUI::redirect("m=dPcabinet&tab=edit_consultation&selConsult=$consult->_id&ajax=$ajax");
    }
}
