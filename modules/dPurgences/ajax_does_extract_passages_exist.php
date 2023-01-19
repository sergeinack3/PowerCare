<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CValue;
use Ox\Mediboard\Urgences\CExtractPassages;

/**
 * Is extract passages exist ?
 */
$extract_passages_id = CValue::get("extract_passages_id");

$extractPassages = new CExtractPassages();

$msg = "";
if ($extract_passages_id) {
  $extractPassages->load($extract_passages_id);

  if ($extractPassages->_id) {
    $msg = $extractPassages->_id;
  }
}

echo json_encode($msg);