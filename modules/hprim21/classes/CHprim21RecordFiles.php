<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use Ox\Core\CMbObject;

/**
 * Class CHprim21RecordPayment 
 * Record payment, message XML
 */
class CHprim21RecordFiles extends CHPrim21MessageXML {
  function getContentNodes() {
    $data = array();

    $this->queryNodes("//P"  , null, $data, true); // get ALL the REG segments


    $this->queryNodes("//OBX", null, $data, true); // get ALL the REG segments

    return $data;
  }
 
  function handle($ack, CMbObject $object, $data) {

    // foreach ($data["P"] as $node)


  }
}