<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle\ObservationResultSet;

use DOMNode;
use Exception;

/**
 * Class HandleORSObservation
 *
 * @package Ox\Interop\Hl7\V2\Handle\ObservationResultSet
 */
class HandleORSObservationPerop extends HandleORSObservationORU
{
    /**
     * @param DOMNode $OBX
     * @param int     $OBX_index
     *
     * @return HandleORS
     * @throws Exception
     */
    protected function getObjectOBXHandle(DOMNode $OBX, int $OBX_index): HandleORSOBX
    {
        // OBX.2 : Type de l'OBX
        $value_type = $this->getOBXValueType($OBX);

        // Treatment OBX
        switch ($value_type) {
            // Pulse Generator and Lead Observation Results
            case "ST":
            case "CWE":
            case "DTM":
            case "NM":
            case "SN":
                return new HandleORSObservationResultsPerop($this->message, $this->observation, $OBX);
            default:
                return parent::getObjectOBXHandle($OBX, $OBX_index);
        }
    }
}
