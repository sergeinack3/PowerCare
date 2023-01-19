<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Sets the values to the session too
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CValue;

CValue::postOrSessionAbs("_conduction");
CValue::postOrSessionAbs("_oreille");

$do = new CDoObjectAddEdit("CExamAudio", "examaudio_id");
$do->doIt();

