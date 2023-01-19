<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CValue;

$cn_receiver_guid = CValue::post("cn_receiver_guid");

if ($cn_receiver_guid == "none") {
  unset($_SESSION["cn_receiver_guid"]);
  return;
}
CValue::setSessionAbs("cn_receiver_guid", $cn_receiver_guid);