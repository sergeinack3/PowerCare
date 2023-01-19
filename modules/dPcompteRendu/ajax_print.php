<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFile;

/**
 * Impression d'un fichier par une imprimante réseau
 */
CCanDo::checkRead();

$printer_guid = CValue::get("printer_guid");
$file_id    = CValue::get("file_id");

$file = new CFile();
$file->load($file_id);

$printer = CStoredObject::loadFromGuid($printer_guid);

$printer->sendDocument($file);
