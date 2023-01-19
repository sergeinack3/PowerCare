<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle\ObservationResultSet;

use DOMNode;
use Exception;
use Ox\Core\CMbObject;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7v2Acknowledgment;
use Ox\Interop\Hl7\Exceptions\V2\CHL7v2ExceptionWarning;
use Ox\Interop\Hl7\V2\Handle\RecordObservationResultSet;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\ObservationResult\CObservationResponsibleObserver;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class HandleORS
{
    /** @var string */
    protected const OBR_ID_PARTNER = 'OBR.id_partner';
    /** @var string */
    protected const OBR_IDENTITY_ID = 'OBR.identity_id';
    /** @var string */
    protected const OBR_UNIVERSAL_SERVICE_ID = 'OBR.4';
    /** @var string */
    protected const OBR_DATETIME = 'OBR.datetime';

    /** @var CInteropSender */
    protected $sender;

    /** @var RecordObservationResultSet */
    protected $message;

    /** @var CExchangeHL7v2 */
    protected $exchange_hl7v2;

    /** @var CHL7v2Acknowledgment */
    protected $ack;

    /** @var array */
    protected $data;

    /** @var CPatient|null */
    protected $patient;

    /** @var CSejour|null */
    protected $sejour;

    /** @var CMbObject */
    protected $target_object;

    /** @var ParameterBag */
    protected $OBR;

    /** @var ParameterBag */
    protected $ORC;

    /** @var ParameterBag */
    protected $TXA;

    /** @var int */
    protected $observation_index;

    /**
     * HandleORS constructor.
     *
     * @param CHL7v2Acknowledgment $ack
     * @param array                $data
     * @param CExchangeHL7v2       $exchange_hl7v2
     * @param CInteropSender     $sender
     */
    public function __construct(RecordObservationResultSet $message)
    {
        $this->message        = $message;
        $this->sender         = $this->message->_ref_sender;
        $this->exchange_hl7v2 = $this->message->_ref_exchange_hl7v2;
        $this->ack            = $this->message->_ref_ack;
        $this->data           = $this->message->_ref_data;
        $this->patient        = $this->message->getPatient();
        $this->sejour         = $this->message->getSejour();
    }

    /**
     * @param string|null $ns
     * @param string|null $id
     *
     * @throws CHL7v2ExceptionWarning
     */
    protected function checkReceivingIdentifiers(?string $ns, ?string $id): void
    {
        // should I test assigned authority
        if (!$this->sender->_configs['check_receiving_application_facility']) {
            return;
        }

        // check namespace
        $sender_ns = $this->sender->_configs['assigning_authority_namespace_id'];
        if ($sender_ns != $ns || !$sender_ns) {
            throw (new CHL7v2ExceptionWarning('E012'))
                ->setPosition($this->getPosition());
        }
    }

    /**
     * @return string
     */
    protected function getPosition(): string
    {
        return "OBSERVATION[$this->observation_index]";
    }

    /**
     * @return CHL7v2Acknowledgment
     */
    public function getAck(): ?CHL7v2Acknowledgment
    {
        return $this->ack;
    }

    /**
     * @return bool
     */
    protected function isModeSAS(): bool
    {
        return (bool) $this->sender->_configs['mode_sas'];
    }

    /**
     * Get observation result status
     *
     * @param DOMNode $OBX DOM node
     *
     * @return string
     * @throws Exception
     */
    protected function getObservationResultStatus(DOMNode $OBX)
    {
        return $this->message->queryTextNode("OBX.11", $OBX);
    }

    /**
     * Get observation value
     *
     * @param DOMNode $node DOM node
     *
     * @return string
     * @throws Exception
     */
    protected function getObservationValue(DOMNode $node)
    {
        return $this->message->queryTextNode("OBX.5", $node);
    }

    /**
     * Used for add an code Information / Warning / Error
     *
     * @param string|array $code array should be [code_error => comment_error]
     */
    protected function addCode($code): void
    {
        $this->message->codes[] = $code;
    }

    /**
     * Get observation date time
     *
     * @param DOMNode $OBX DOM node
     *
     * @return string
     * @throws Exception
     */
    protected function getOBXObservationDateTime(DOMNode $OBX)
    {
        return $this->message->queryTextNode("OBX.14/TS.1", $OBX);
    }

    /**
     * Get observation Sub-id
     *
     * @param DOMNode $node DOM node
     *
     * @return string
     * @throws Exception
     */
    protected function getOBXObservationSubID(DOMNode $OBX)
    {
        return $this->message->queryTextNode("OBX.4", $OBX);
    }

    /**
     * Get PV1-50
     *
     * @param DOMNode $node
     *
     * @return string
     * @throws Exception
     */
    protected function getPV150(DOMNode $node)
    {
        return $this->message->queryTextNode("PV1.50/CX.1", $node);
    }

    /**
     * Get observation date time
     *
     * @param DOMNode $node DOM node
     * @param string  $CE   CE
     *
     * @return string
     * @throws Exception
     */
    protected function getOBRServiceIdentifier(ParameterBag $OBR, string $CE = 'CE.1')
    {
        $OBR_4 = $OBR->get(self::OBR_UNIVERSAL_SERVICE_ID);

        return $this->message->queryTextNode($CE, $OBR_4);
    }

    /**
     * Return the author of the document
     *
     * @param DOMNode $node node
     *
     * @return String
     * @throws Exception
     */
    protected function getObservationAuthor(DOMNode $OBX)
    {
        $XCNs = $this->message->queryNodes('OBX.16', $OBX);
        if (!$XCNs || $XCNs->length === 0) {
            return null;
        }

        return $this->message->getDoctor($XCNs, new CMediusers(), false);
    }

    /**
     * Get value type
     *
     * @param DOMNode $node DOM node
     *
     * @return string
     * @throws Exception
     */
    protected function getOBXValueType(DOMNode $node)
    {
        return $this->message->queryTextNode("OBX.2", $node);
    }

    /**
     * @param DOMNode $node
     *
     * @return string
     * @throws Exception
     */
    protected function getOBXObservationIdentifier(DOMNode $node)
    {
        return $this->message->queryTextNode("OBX.3/CE.1", $node);
    }

    /**
     * @param ParameterBag $bag
     */
    public function handle(ParameterBag $bag): void
    {
        // keep OBR
        if ($OBR = $bag->get('OBR')) {
            $this->OBR = $OBR;
        }

        // keep ORC
        if ($ORC = $bag->get('ORC')) {
            $this->ORC = $ORC;
        }

        // keep target object
        if ($target_object = $bag->get('target_object')) {
            $this->target_object = $target_object;
        }

        // Observation index
        if (($observation_index = $bag->getInt('OBSERVATION.index')) !== null) {
            $this->observation_index = $observation_index;
        }
    }

    /**
     * Try to find Responsible from rpps or family and given name
     *
     * @param DOMNode $responsible_node
     *
     * @return CObservationResponsibleObserver|null
     * @throws Exception
     */
    protected function findOrCreateResponsible(DOMNode $responsible_node): ?CObservationResponsibleObserver
    {
        $type_identifier = $this->message->queryTextNode("XCN.13", $responsible_node);
        $identifier      = $this->message->queryTextNode('XCN.1', $responsible_node);
        switch ($type_identifier) {
            case 'RPPS':
                if ($medecin = CMedecin::getFromRPPS($identifier)) {
                    $responsible = CObservationResponsibleObserver::getFromMedecin($medecin);
                    break;
                }

                // search by name
            default:
                $family           = $this->message->queryTextNode("XCN.2", $responsible_node);
                $given            = $this->message->queryTextNode("XCN.3", $responsible_node);
                if (!$validator_name = "$family $given") {
                    return null;
                }

                $responsible     = new CObservationResponsibleObserver();
                $responsible->id = substr(sha1($validator_name), 0, 15);
                if (!$responsible->loadMatchingObject()) {
                    $responsible->family_name = $family;
                    $responsible->given_name  = $given;
                    $responsible->suffix      = $this->message->queryTextNode("XCN.5", $responsible_node);
                    $responsible->prefix      = $this->message->queryTextNode("XCN.6", $responsible_node);
                    if ($msg = $responsible->store()) {
                        $this->message->codes[] = (new CHL7v2ExceptionWarning('E304'))
                            ->setComments($msg)
                            ->setPosition($this->getPosition())
                            ->getWarning();
                    }
                }
        }

        return $responsible->_id ? $responsible : null;
    }
}
