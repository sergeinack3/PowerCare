<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/*
if(isset($_POST["date"]) && $_POST["date"] == "now") {
  $_POST["date"] = CMbDT::dateTime();
}
*/

use Ox\Core\CDoObjectAddEdit;

$do = new CDoObjectAddEdit("CTransmissionMedicale", "transmission_medicale_id");
$do->doIt();