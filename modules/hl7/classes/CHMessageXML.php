<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use Ox\Core\CMbObject;

/**
 * Interface CHMessageXML
 * Message XML
 */
interface CHMessageXML {
  function getContentNodes();
  
  function handle($ack, CMbObject $object, $data);
}
