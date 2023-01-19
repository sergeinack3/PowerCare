<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Class CHL7v2Reader
 */
class CHL7v2Reader implements IShortNameAutoloadable {
  /**
   * Read HL7 file
   *
   * @param string $fileName Filename
   *
   * @return string
   * @throws Exception
   */
  function readFile($fileName) {
    $message = new CHL7v2Message();
    
    try {
      $fileContents = file_get_contents($fileName);
      $message->parse($fileContents);
    }
    catch (Exception $e) {
      exceptionHandler($e);
    }
  
    return $message;
  }
}
