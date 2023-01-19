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
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\Exceptions\V2\CHL7v2ExceptionWarning;
use Ox\Interop\Hl7\Handle;
use Ox\Interop\Hl7\V2\Handle\RecordObservationResultSet;
use Ox\Mediboard\ObservationResult\CObservationAbnormalFlag;
use Ox\Mediboard\ObservationResult\CObservationIdentifier;
use Ox\Mediboard\ObservationResult\CObservationResponsibleObserver;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultValue;
use Ox\Mediboard\ObservationResult\CObservationValueUnit;
use Ox\Mediboard\ObservationResult\Interop\ObservationAbNormalManager;
use Ox\Mediboard\ObservationResult\Interop\ObservationReferenceRangeResolver;
use Ox\Mediboard\Ucum\Ucum;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class HandleORSObservationResults
 *
 * @package Ox\Interop\Hl7\V2\Handle\ObservationResultSet
 */
class HandleORSObservationResultsLabo extends HandleORSObservationResults
{
    /** @var string[] */
    private const SUPPORTED_STATUS = ['P', 'F', 'D', 'X', 'C'];

    /** @var string OBX-11 : P(préliminaire)|F(final)|C(correction)|D(suppression resultat)|X(suppression analyse) */
    protected $status;

    /** @var string|null OBX-2 : CE(coded)|NM(num)|SN(num interval)|TS(timetamps)|TX(textuel) */
    protected $type;

    /** @var bool */
    protected $is_new;

    public function __construct(RecordObservationResultSet $message, ParameterBag $observation, DOMNode $OBX)
    {
        parent::__construct($message, $observation, $OBX);

        $this->type   = $this->message->queryTextNode('OBX.2', $OBX);
        $this->status = $this->message->queryTextNode('OBX.11', $OBX);
        $this->is_new = true;
    }

    /**
     * @param ParameterBag $bag
     *
     * @throws CHL7v2Exception
     */
    public function handle(ParameterBag $bag): void
    {
        parent::handle($bag);

        if (!$this->observation_result_set) {
            throw (new CHL7v2ExceptionWarning('E310'))
                ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
        }

        // search result
        if (!$observation_result = $this->findResult()) {
            $observation_result = new CObservationResult();
        }

        // here it's possible to check if status change it's possible

        // Always update status
        $this->mapRequiredElements($observation_result);

        // map observation si new || si status != (X|D)
        if ($this->is_new || (!$this->is_new && !in_array($this->status, ['X', 'D']))) {
            $this->mapIdentifiers($observation_result);

            $this->mapResult($observation_result);
        }

        // responsible
        $responsible_nodes = $this->message->queryNodes("OBX.16", $this->OBX);
        if ($responsible_nodes->count() > 0) {
            $responsible_node = $responsible_nodes->item(0);

            if ($responsible = $this->findOrCreateResponsible($responsible_node)) {
                $observation_result->responsible_observer_id = $responsible->_id;
            }
        } elseif ($responsible = $this->OBR->get(CObservationResponsibleObserver::class)) {
            $observation_result->responsible_observer_id = $responsible->_id;
        }

        if ($msg = $observation_result->store()) {
            throw (new CHL7v2ExceptionWarning('E304'))
                ->setComments($msg)
                ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
        }

        // Ab normal Flags
        $this->handleFlags($observation_result);
    }

    /**
     * @return CObservationResult|null
     * @throws Exception
     */
    protected function findResult(): ?CObservationResult
    {
        $identifier = $this->matchIdentifiers();
        if (!$identifier->_id) {
            return null;
        }

        $observation_result                            = new CObservationResult();
        $observation_result->observation_result_set_id = $this->observation_result_set->_id;
        $observation_result->identifier_id             = $identifier->_id;
        $observation_result->sub_identifier            = $this->message->queryTextNode('OBX.4', $this->OBX);
        if (!$observation_result->loadMatchingObject()) {
            return null;
        }

        $this->is_new = false;

        return $observation_result;
    }

    /**
     * @param CObservationResult $observation_result
     *
     * @return CObservationResultValue|null
     * @throws CHL7v2Exception
     * @throws CHL7v2ExceptionWarning
     */
    protected function mapResult(CObservationResult $observation_result): ?CObservationResultValue
    {
        $observation_value = new CObservationResultValue();
        $observation_value->observation_result_id = $observation_result->_id;

        if ($reference = $this->message->queryTextNode('OBX.7', $this->OBX)) {
            [$low, $high] = (new ObservationReferenceRangeResolver())->resolve($reference);
            $observation_value->setReferenceRange($low, $high);
        }

        if ($unit = $this->mapUnit()) {
            $observation_value->unit_id = $unit->_id;
        }

        $observation_value->value = $this->message->queryTextNode('OBX.5', $this->OBX);

        if (!$observation_value->loadMatchingObjectEsc()) {
            if ($msg = $observation_value->store()) {
                throw (new CHL7v2ExceptionWarning('E304'))
                    ->setComments($msg)
                    ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
            }
        }

        return $observation_value;
    }

    /**
     * @param CObservationResult $observation_result
     *
     * @throws CHL7v2Exception
     */
    protected function mapRequiredElements(CObservationResult $observation_result): void
    {
        if (!in_array($this->status, self::SUPPORTED_STATUS)) {
            throw (new CHL7v2ExceptionWarning('E307'))
                ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
        }

        // Observation result set
        $observation_result->observation_result_set_id = $this->observation_result_set->_id;

        // status (OBX.11)
        $observation_result->status   = $this->status;

        // datetime (OBX.14)
        if ($datetime = $this->message->queryTextNode('OBX.14', $this->OBX)) {
            $observation_result->datetime = CMbDT::dateTime($datetime);
        }

        $observation_result->method          = $this->message->queryTextNode('OBX.17', $this->OBX);
        $observation_result->sub_identifier  = $this->message->queryTextNode('OBX.4', $this->OBX);
    }

    /**
     * @param CObservationResult $observation_result
     *
     * @throws Exception
     */
    protected function mapIdentifiers(CObservationResult $observation_result): void
    {
        $identifier = $this->matchIdentifiers();
        if (!$identifier->_id) {
            if ($msg = $identifier->store()) {
                throw (new CHL7v2ExceptionWarning('E304'))
                    ->setComments($msg)
                    ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
            }
        }

        $observation_result->identifier_id = $identifier->_id;
    }

    /**
     * @return CObservationIdentifier
     * @throws Exception
     */
    protected function matchIdentifiers(): CObservationIdentifier
    {
        $id    = $this->message->queryTextNode("OBX.3/CE.1", $this->OBX);
        $coding_system = $this->message->queryTextNode("OBX.3/CE.3", $this->OBX);

        if (!$id || !$coding_system) {
            throw (new CHL7v2ExceptionWarning('E010'))
                ->setComments('CE.1|CE.2|CE.3')
                ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]/CE");
        }
        $text = $this->message->queryTextNode("OBX.3/CE.2", $this->OBX);

        $identifier                = new CObservationIdentifier();
        $identifier->identifier    = $id;
        $identifier->text          = $text;
        $identifier->coding_system = $coding_system;
        if (!$identifier->loadMatchingObject()) {
            $identifier->alt_identifier    = $this->message->queryTextNode("OBX.3/CE.4", $this->OBX);
            $identifier->alt_text          = $this->message->queryTextNode("OBX.3/CE.5", $this->OBX);
            $identifier->alt_coding_system = $this->message->queryTextNode("OBX.3/CE.6", $this->OBX);
        }

        return $identifier;
    }

    /**
     * @param CObservationResult $observation_result
     *
     * @throws CHL7v2Exception
     */
    protected function mapUnit(): ?CObservationValueUnit
    {
        $is_unit_handle = in_array($this->type, ['NM', 'SN']) && $this->message->queryNode('OBX.5', $this->OBX);
        if (!$is_unit_handle) {
            return null;
        }

        $units = $this->message->queryTextNode("OBX.6/CE.1", $this->OBX);
        if (!$units) {
            return null;
        }

        // validate Ucum unit
        $ucum          = new Ucum();
        $is_valid_ucum = $ucum->callValidation($units, true);

        // Retrieve Observation Unit
        $identifier      = $units;
        $coding_system   = $is_valid_ucum ? Ucum::CODE_SYSTEM : CObservationValueUnit::SYSTEM_UNKNOWN;
        $text            = $this->message->queryTextNode("OBX.6/CE.2", $this->OBX);
        $unit_type       = new CObservationValueUnit();
        if (!$unit_type->loadMatch($identifier, $coding_system, $text ?: $identifier)) {
            throw (new CHL7v2ExceptionWarning('E304'))
                ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
        }

        return $unit_type;
    }

    /**
     * @param CObservationResult|null $observation_result
     *
     * @return void
     * @throws Exception
     */
    private function handleFlags(CObservationResult $observation_result): void
    {
        $flag_nodes = $this->message->query("OBX.8", $this->OBX);
        $flags = [];
        foreach ($flag_nodes as $flag_node) {
            $flag = $this->message->queryTextNode(".", $flag_node);
            if (in_array($flag, CObservationAbnormalFlag::$flags)) {
                $flags[] = $flag;
            }
        }

        if (!$flags) {
            return;
        }

        $manager_ab_normal = new ObservationAbNormalManager();
        if (!$manager_ab_normal->synchronizeFlags($observation_result, $flags)) {
            foreach ($manager_ab_normal->getErrorStoringProcess() as $msg_error) {
                $this->message->codes[] =
                    (new CHL7v2ExceptionWarning('E311'))
                        ->setComments($msg_error)
                        ->setPosition($this->getPosition())
                        ->getWarning();
            }

            foreach ($manager_ab_normal->getErrorDeletingProcess() as $msg_error) {
                $this->message->codes[] = (new CHL7v2ExceptionWarning('E312'))
                    ->setComments($msg_error)
                    ->setPosition($this->getPosition())
                    ->getWarning();
            }
        }
    }
}
