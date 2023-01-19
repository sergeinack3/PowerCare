<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle\ObservationResultSet;

use DOMNode;
use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\Exceptions\V2\CHL7v2ExceptionWarning;
use Ox\Interop\Hl7\Handle;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\ObservationResult\CObservationValueType;
use Ox\Mediboard\ObservationResult\CObservationValueUnit;
use Ox\Mediboard\Patients\CPatient;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class HandleORSObservationResults
 *
 * @package Ox\Interop\Hl7\V2\Handle\ObservationResultSet
 */
class HandleORSObservationResultsPerop extends HandleORSObservationResults
{
    /**
     * @param ParameterBag $bag
     *
     * @throws CHL7v2Exception
     */
    public function handle(ParameterBag $bag): void
    {
        parent::handle($bag);

        $result_date = $bag->get('result_date');

        $this->getPulseGeneratorAndLeadObservationResults(
            $this->OBX,
            $this->patient,
            $this->target_object,
            $result_date
        );
    }

    /**
     * OBX Segment pulse generator and lead observation results
     *
     * @param DOMNode       $OBX            DOM node
     * @param CPatient      $patient        Person
     * @param CStoredObject $object         Opération
     * @param string        $dateTimeResult Date
     *
     * @return bool
     * @throws Exception
     */
    private function getPulseGeneratorAndLeadObservationResults(
        DOMNode $OBX,
        CPatient $patient,
        CStoredObject $object,
        ?string $dateTimeResult
    ) {
        $result_set = new CObservationResultSet();

        if ($dateTimeResult) {
            $result_set->patient_id    = $patient->_id;
            $result_set->context_class = $object->_class;
            $result_set->context_id    = $object->_id;
            $result_set->datetime      = CMbDT::dateTime($dateTimeResult);
            $result_set->sender_id     = $this->sender->_id;
            $result_set->sender_class  = $this->sender->_class;
            if (!$result_set->loadMatchingObject()) {
                if ($msg = $result_set->store()) {
                    throw (new CHL7v2ExceptionWarning('E302'))
                        ->setComments($msg)
                        ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
                }
            }
        }

        // Traiter le cas où ce sont des paramètres sans résultat utilisable
        if ($this->getObservationResultStatus($OBX) === "X") {
            return true;
        }

        $result                            = new CObservationResult();
        $result->observation_result_set_id = $result_set->_id;
        $this->mappingObservationResult($OBX, $result);

        if ($msg = $result->store()) {
            throw (new CHL7v2ExceptionWarning('E304'))
                ->setComments($msg)
                ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
        }

        return true;
    }

    /**
     * Get observation date time
     *
     * @param DOMNode            $node   DOM node
     * @param CObservationResult $result Result
     *
     * @throws Exception
     */
    private function mappingObservationResult(DOMNode $node, CObservationResult $result)
    {
        // OBX-3: Observation Identifier
        $this->getObservationIdentifier($node, $result);

        // OBX-6: Units
        $this->getUnits($node, $result);

        // OBX-5: Observation Value (Varies)
        $result->_value = $this->getObservationValue($node);

        // OBX-11: Observation Result Status
        $result->status = $this->getObservationResultStatus($node);
    }


    /**
     * Get observation identifier
     *
     * @param DOMNode            $node   DOM node
     * @param CObservationResult $result Result
     *
     * @throws Exception
     */
    private function getObservationIdentifier(DOMNode $node, CObservationResult $result): void
    {
        $identifier    = $this->message->queryTextNode("OBX.3/CE.1", $node);
        $text          = $this->message->queryTextNode("OBX.3/CE.2", $node);
        $coding_system = $this->message->queryTextNode("OBX.3/CE.3", $node);

        $value_type             = new CObservationValueType();
        $result->_value_type_id = $value_type->loadMatch($identifier, $coding_system, $text);
    }

    /**
     * Get unit
     *
     * @param DOMNode            $node   DOM node
     * @param CObservationResult $result Result
     *
     * @throws Exception
     */
    private function getUnits(DOMNode $node, CObservationResult $result)
    {
        $identifier    = $this->message->queryTextNode("OBX.6/CE.1", $node);
        $text          = $this->message->queryTextNode("OBX.6/CE.2", $node);
        $coding_system = $this->message->queryTextNode("OBX.6/CE.3", $node);

        $unit_type       = new CObservationValueUnit();
        $result->_unit_id = $unit_type->loadMatch($identifier, $coding_system, $text);
    }
}
