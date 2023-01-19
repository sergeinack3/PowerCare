<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Core\FileUtil\CMbvCardExport;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CValue::get("patient_id");

$patient = new CPatient();
$patient->load($patient_id);

$vcard = new CMbvCardExport();
$vcard->saveVCard($patient);
