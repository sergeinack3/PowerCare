<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle\ObservationResultSet;

use DOMNode;
use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\Repository\ConsultationRepository;
use Ox\Interop\Eai\Repository\OperationRepository;
use Ox\Interop\Eai\Repository\SejourRepository;
use Ox\Interop\Eai\Resolver\FileTargetResolver;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\Exceptions\V2\CHL7v2ExceptionError;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class HandleORSObservation
 *
 * @package Ox\Interop\Hl7\V2\Handle\ObservationResultSet
 */
abstract class HandleORSObservation extends HandleORS
{
    /** @var ParameterBag */
    protected $observation;

    /**
     * @param ParameterBag $parameters
     *
     * @return ParameterBag
     */
    protected function getParameters(ParameterBag $parameters): ParameterBag
    {
        return $parameters;
    }

    /**
     * @param DOMNode $OBX
     *
     * @return CMbObject|null
     * @throws CHL7v2Exception
     * @throws Exception
     */
    protected function determineTarget(DOMNode $OBX): ?CMbObject
    {
        static $search = true;

        if ($this->target_object || !$search) {
            return $this->target_object;
        }

        if ($this->isModeSAS()) {
            // prevent to try found target which not possible to find
            $search = false;

            return $this->target_object = null;
        }

        // Cas où l'on force le rattachement :
        $object_attach_OBX = $this->sender->_configs["object_attach_OBX"];
        $patient           = $this->patient;
        $sejour            = $this->sejour;
        $OBX_datetime      = $this->getOBXObservationDateTime($OBX);
        $date              = $this->OBR ? ($this->OBR->get(self::OBR_DATETIME) ?: $OBX_datetime) : $OBX_datetime;
        $date              = CMbDT::dateTime($date);

        // OBX.16 : Identifiant du praticien
        $praticien_id = $this->getObservationAuthor($OBX);

        // Sub id
        $observation_sub_id = $this->getOBXObservationSubID($OBX);

        $repository_operation = (new OperationRepository())
            ->setPatient($patient)
            ->setSejour($sejour)
            ->setPraticienId($praticien_id)
            ->setDateOperation($date);

        $repository_consultation = (new ConsultationRepository())
            ->setPatient($patient)
            ->setSejour($sejour)
            ->setPraticienId($praticien_id)
            ->setDateConsultation($date);

        $repository_sejour = (new SejourRepository(SejourRepository::STRATEGY_ONLY_DATE))
            ->setSejourFound($this->sejour)
            ->setPatient($patient)
            ->setDateSejour($date);

        $object = (new FileTargetResolver())
            ->setPatient($patient)
            ->setModeSas($this->isModeSAS())
            ->setId400Category($observation_sub_id)
            ->setConsultationRepository($repository_consultation)
            ->setOperationRepository($repository_operation)
            ->setSejourRepository($repository_sejour)
            ->resolve($this->sender, $object_attach_OBX ?? '');

        // resolver could find a sejour
        if (!$this->sejour) {
            $this->sejour = $repository_sejour->getSejour();
        }

        // Cas où le rattachement est au patient et qu'on ne l'a pas retrouvé, et qu'on n'est pas en mode SAS
        if (!$object && ($object_attach_OBX == "CPatient")) {
            throw CHL7v2ExceptionError::ackAR($this->exchange_hl7v2, $this->ack, 'E219');
        }

        if (!$this->sejour && $this->sender->_configs["object_attach_OBX"] != "CPatient") {
            if (!$sejour || !$sejour->_id) {
                throw CHL7v2ExceptionError::ackAR($this->exchange_hl7v2, $this->ack, 'E220');
            }
        }

        // Rattachement à une intervention/séjour dans le cas de TAMM-SIH (mode SAS et non)
        $is_tamm_sih = CMbArray::get($this->sender->_configs, "handle_tamm_sih");
        if (CModule::getActive('oxSIHCabinet') && $is_tamm_sih && $this->data["PV1"]) {
            if ($venueRI = $this->message->getVenueRI()) {
                $sejour = new CSejour();
                $sejour->load($venueRI);
                if ($sejour->_id) {
                    $object = $sejour;
                }
            }

            $PV1_50 = $this->getPV150($this->data["PV1"]);
            if ($PV1_50) {
                $operation = new COperation();
                $operation->load($PV1_50);
                if ($operation->_id) {
                    $object = $operation;
                }
            }
        }

        // keep objects retrieved
        $this->sejour  = $sejour;
        $this->patient = $patient;

        return $this->target_object = $object;
    }

    /**
     * Get observation date time
     *
     * @param DOMNode $node DOM node
     *
     * @return string
     * @throws Exception
     */
    protected function getOBRObservationDateTime(DOMNode $node)
    {
        return $this->message->queryTextNode("OBR.7", $node);
    }

    /**
     * Get id document of partner
     *
     * @param DOMNode $node DOM node
     *
     * @return string
     * @throws Exception
     */
    protected function getIdDocumentPartner(DOMNode $node)
    {
        return $this->message->queryTextNode("OBR.18", $node);
    }
}
