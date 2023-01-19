<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Interop\Hl7\CHL7v2TableDescription;
use Ox\Interop\Hl7\Events\SVS\CHL7v3EventSVSValueSet;

$table_id = $_GET["table_id"];

$table_description = new CHL7v2TableDescription();
$table_description->number = $table_id;

if (!$table_description->loadMatchingObject()) {
  return;
}
$table_description->loadEntries();

$event_svs_value_set = new CHL7v3EventSVSValueSet();
$event_svs_value_set->build($table_description);
