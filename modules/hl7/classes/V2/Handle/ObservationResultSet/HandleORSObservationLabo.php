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
use Ox\Interop\Hl7\Exceptions\V2\CHL7v2ExceptionError;
use Ox\Interop\Hl7\Exceptions\V2\CHL7v2ExceptionWarning;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\ObservationResult\Interop\ObservationResultSetLocator;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class HandleORSObservation
 *
 * @package Ox\Interop\Hl7\V2\Handle\ObservationResultSet
 */
class HandleORSObservationLabo extends HandleORSObservationORU
{
    /** @var string */
    public const ORC_TRIGGER_EVENT = 'ORC.trigger_event';
    /** @var string */
    public const ORC_OBS_RESULT_SET_IDENTIFIER = 'ORC.observation_result_set_identifier';
    /** @var string */
    public const ORC_OBS_RESULT_SET_DATETIME = 'ORC.observation_result_set_datetime';

    /** @var CObservationResultSet */
    protected $observation_result_set;

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

        if ($this->isHandle($OBX, $value_type)) {
            return new HandleORSObservationResultsLabo($this->message, $this->observation, $OBX);
        }

        // Cas des Files
        if (in_array($value_type, ['RP', 'ED'])) {
            return parent::getObjectOBXHandle($OBX, $OBX_index);
        }

        throw (new CHL7v2ExceptionWarning('E309'))
            ->setPosition("OBSERVATION[$this->observation_index]/OBX[$OBX_index]");
    }

    /**
     * @param ParameterBag $parameters
     *
     * @return ParameterBag
     * @throws CHL7v2ExceptionError
     * @throws CHL7v2ExceptionWarning
     */
    protected function getParameters(ParameterBag $parameters): ParameterBag
    {
        if (!$this->patient) {
            if ($this->isModeSAS()) {
                return $parameters;
            }

            throw CHL7v2ExceptionError::ackAR($this->exchange_hl7v2, $this->ack, 'E219');
        }

        if (!$this->observation_result_set) {
            $this->observation_result_set = $this->findOrCreateObservationResultSet();
        }

        $parameters->set('observation_result_set', $this->observation_result_set);

        return parent::getParameters($parameters);
    }

    /**
     * @return CObservationResultSet
     * @throws CHL7v2ExceptionWarning
     */
    protected function findOrCreateObservationResultSet(): CObservationResultSet
    {
        $identifier = $this->ORC->get(self::ORC_OBS_RESULT_SET_IDENTIFIER);

        try {
            $result_set_locator = (new ObservationResultSetLocator($identifier, $this->sender, $this->patient))
                ->setTarget($this->target_object)
                ->setIdentifierSejour($this->message->venueAN ?: null)
                ->setLaboNumber($this->OBR->get(self::OBR_IDENTITY_ID))
                ->setDatetime($this->ORC->get(self::ORC_OBS_RESULT_SET_DATETIME));

            $obs_result_set = $result_set_locator->findOrCreate();
        } catch (Exception $e) {
            throw (new CHL7v2ExceptionWarning('E303'))
                ->setComments($e->getMessage())
                ->setPosition("OBSERVATION[$this->observation_index]");
        }

        return $obs_result_set;
    }

    /**
     * @param DOMNode $OBX
     * @param string  $value_type
     *
     * @return bool
     * @throws Exception
     */
    private function isHandle(DOMNode $OBX, string $value_type): bool
    {
        // OBX.2 peut ne pas être fournit si annulation
        $OBX_11    = $this->message->queryTextNode('OBX.11', $OBX);
        $is_cancel = ($OBX_11 === 'X');
        if (!$OBX_11) {
            return $is_cancel;
        }

        $types_handled = ['CE', 'NM', 'SN', 'TS', 'TX'];

        return in_array($value_type, $types_handled);
    }

    /**
     * @param DOMNode|null $node_ORC
     *
     * @throws Exception
     */
    protected function handleORC(?DOMNode $node_ORC): ParameterBag
    {
        if (!$node_ORC) {
            throw (new CHL7v2ExceptionWarning('E009'))
                ->setPosition("OBSERVATION[$this->observation_index]/ORC");
        }

        $ORC = parent::handleORC($node_ORC);

        // ORC.1 : Evénement déclencheur
        $trigger_evt = $this->message->queryTextNode("ORC.1", $node_ORC);

        // ORC.4 : Identifiant de la demande d'examen du Requérant
        $ORC_4 = $this->message->queryTextNode('ORC.4/EI.1', $node_ORC);

        // ORC.9 : Date/Time Of Transaction
        $ORC_9 = $this->message->queryTextNode('ORC.9', $node_ORC);

        // ORC.37 : Date de prescription
        $ORC_37 = $this->message->queryTextNode('ORC.37', $node_ORC);

        // ORC.38 : Identifiant de la demande d'examens côté Exécutant
        $ORC_38_id = $this->message->queryTextNode('ORC.38/EI.1', $node_ORC);
        $ORC_38_ns = $this->message->queryTextNode('ORC.38/EI.2', $node_ORC);

        // Identifiant de la demande d'examens / ORC.38 ==> idex sur le resultset
        $this->checkReceivingIdentifiers($ORC_38_ns, $ORC_38_id);

        if (!($obs_result_set_id400 = $ORC_38_id)) {
            throw (new CHL7v2ExceptionWarning('E306'))
                ->setPosition("OBSERVATION[$this->observation_index]/ORC.38");
        }
        $obs_result_set_id400 .= ($ORC_38_ns ? "-$ORC_38_ns" : '');

        // ORC4 ==> config HL7 mediboard ===> est que c est bien mon namespace ? oui, l'id existe-t-il ?

        $datetime = $ORC_9 ?: $ORC_37;

        $ORC->set(self::ORC_OBS_RESULT_SET_DATETIME, $datetime ? CMbDT::dateTime($datetime) : null);
        $ORC->set(self::ORC_TRIGGER_EVENT, $trigger_evt);
        $ORC->set(self::ORC_OBS_RESULT_SET_IDENTIFIER, $obs_result_set_id400);

        return $ORC;
    }

    /**
     * @param DOMNode $node_OBR
     *
     * @return ParameterBag
     * @throws Exception
     */
    protected function handleOBR(DOMNode $node_OBR): ParameterBag
    {
        $OBR = parent::handleOBR($node_OBR);

        // OBR.2 : Numéro de l'examen demandé <N°deamnde>-<rang examen>

        // OBR.4 : Code de l'examen demandé (LOINC) ==> self::OBR_UNIVERSAL_SERVICE_ID

        // OBR.7 : Observation Date/Time
        $OBR_7 = $this->message->queryTextNode('OBR.7', $node_OBR);

        // OBR.16 Prescripteur

        // OBR.10 Préleveur

        // update ORC if datetime is null
        if (!$this->ORC->get(self::ORC_OBS_RESULT_SET_DATETIME)) {
            $datetime = $OBR_7 ? CMbDT::dateTime($OBR_7) : CMbDT::dateTime();
            $this->ORC->set(self::ORC_OBS_RESULT_SET_DATETIME, $datetime);
        }

        $responsible_nodes = $this->message->queryNodes('OBR.10', $node_OBR);
        if ($responsible_nodes->count() > 0) {
            $responsible_node = $responsible_nodes->item(0);
            if ($responsible = $this->findOrCreateResponsible($responsible_node)) {
                $OBR->set(get_class($responsible), $responsible);
            }
        }

        return $OBR;
    }
}
