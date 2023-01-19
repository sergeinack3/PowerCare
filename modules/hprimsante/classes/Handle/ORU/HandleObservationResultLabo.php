<?php

/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Handle\ORU;

use DOMNode;
use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hprimsante\Exceptions\CHPrimSanteExceptionWarning;
use Ox\Mediboard\ObservationResult\CObservationAbnormalFlag;
use Ox\Mediboard\ObservationResult\CObservationIdentifier;
use Ox\Mediboard\ObservationResult\CObservationResponsibleObserver;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultExamen;
use Ox\Mediboard\ObservationResult\CObservationResultValue;
use Ox\Mediboard\ObservationResult\CObservationValueUnit;
use Ox\Mediboard\ObservationResult\Interop\ObservationAbNormalManager;
use Ox\Mediboard\ObservationResult\Interop\ObservationReferenceRangeResolver;
use Ox\Mediboard\ObservationResult\Interop\ObservationResultLocator;

class HandleObservationResultLabo extends HandleObservationResult
{
    /** @var string[] */
    private const SUPPORTED_STATUS = ['R', 'P', 'F', 'I', 'D', 'X', 'C', 'U'];

    /** @var DOMNode */
    protected $OBX_node;

    /**
     * @param array $params
     *
     * @throws CHL7v2Exception
     * @throws Exception
     */
    public function handle(array $params): void
    {
        $this->params = $params;

        $this->observation_result_set = $observation_result_set =
            $params[HandleObservation::KEY_OBSERVATION_RESULT_SET] ?? null;
        // si mode sas && !CObservationResultSet just skip integration
        if (!$observation_result_set && $this->isModeSAS()) {
            return;
        } elseif (!$observation_result_set) { // else if mode sas not active, stop integration
            throw $this->makeImportantError('OBR', '19', '9.3');
        }

        if (!$this->OBX_node = $OBX_node = ($params[self::KEY_OBX_NODE] ?? null)) {
            throw $this->makeError('OBX', '18');
        }

        $sub_identifier = $this->message->queryTextNode('OBX.4', $OBX_node);
        $identifier     = $this->mapIdentifier();

        try {
            $observation_result = (new ObservationResultLocator($observation_result_set))
                ->findOrNew($identifier, $sub_identifier);
        } catch (CMbException $exception) {
            throw $this->makeError('OBX', '22', null, $exception->getMessage());
        }

        // Always update status
        $this->mapRequiredElements($observation_result);

        // Responsible for result (validator)
        if ($responsible = $this->findOrCreateResponsible()) {
            $observation_result->responsible_observer_id = $responsible->_id;
        }

        // Result Examen
        $this->mapResultExamen($observation_result);

        if ($msg = $observation_result->store()) {
            throw $this->makeError('OBX', '22', null, $msg);
        }

        $observation_unit = $this->mapUnit();

        // map and store value
        $this->mapResult($observation_result, $observation_unit);

        // AbNormal flag
        $this->mapResultObservationAbNormal($observation_result);

        // notify that result was correctly handled
        $this->message->addResultTreated($observation_result);
    }

    /**
     * @param CObservationResult         $observation_result
     * @param CObservationValueUnit|null $observation_unit
     *
     * @throws CHPrimSanteExceptionWarning
     */
    protected function mapResult(
        CObservationResult $observation_result,
        ?CObservationValueUnit $observation_unit = null
    ): void {

        $OBX_nodes = $this->message->queryNodes('OBX.5', $this->OBX_node);
        $content = '';
        foreach ($OBX_nodes as $node) {
            $content .= ($content ? "\n" : '') . $this->message->queryTextNode('.', $node);
        }

        $content = $this->parseContent($content);

        $observation_value                        = new CObservationResultValue();
        $observation_value->value                 = $content;
        $observation_value->unit_id               = $observation_unit ? $observation_unit->_id : null;
        $observation_value->observation_result_id = $observation_result->_id;

        if ($reference_range = $this->message->queryTextNode('OBX.7', $this->OBX_node)) {
            [$low, $high] = ((new ObservationReferenceRangeResolver())->resolve($reference_range));
            if ($low !== null || $high !== null) {
                $observation_value->setReferenceRange($low, $high);
            }
        }

        if (!$observation_value->loadMatchingObjectEsc()) {
            if ($msg = $observation_value->store()) {
                throw $this->makeError('OBX', '22', '10.6', $msg);
            }
        }
    }

    /**
     * @param CObservationResult $observation_result
     *
     * @throws CHPrimSanteExceptionWarning
     */
    protected function mapRequiredElements(CObservationResult $observation_result): void
    {
        $status = $this->message->queryTextNode('OBX.11', $this->OBX_node);
        if (!in_array($status, self::SUPPORTED_STATUS)) {
            throw $this->makeError('OBX', '21', '10.11');
        }

        // status (OBX.11)
        $observation_result->status = $status;

        // datetime (OBX.14)
        if ($datetime = $this->message->queryTextNode("OBX.14", $this->OBX_node)) {
            $observation_result->datetime = CMbDT::dateTime($datetime);
        }
    }

    /**
     * @return CObservationIdentifier
     * @throws Exception
     */
    protected function mapIdentifier(): CObservationIdentifier
    {
        $id            = $this->message->queryTextNode("OBX.3/CE.1", $this->OBX_node);
        $coding_system = $this->message->queryTextNode("OBX.3/CE.3", $this->OBX_node);
        $text          = $this->message->queryTextNode("OBX.3/CE.2", $this->OBX_node);

        if (!$id || !$coding_system) {
            throw $this->makeError('OBX', "22", '10.4');
        }

        $identifier                    = new CObservationIdentifier();
        $identifier->identifier        = $id;
        $identifier->text              = $text ?: $id;
        $identifier->coding_system     = $coding_system;
        $identifier->alt_identifier    = $this->message->queryTextNode("OBX.3/CE.4", $this->OBX_node) ?: null;
        $identifier->alt_text          = $this->message->queryTextNode("OBX.3/CE.5", $this->OBX_node) ?: null;
        $identifier->alt_coding_system = $this->message->queryTextNode("OBX.3/CE.6", $this->OBX_node) ?: null;

        return $identifier;
    }

    /**
     * @return CObservationValueUnit|null
     * @throws CHPrimSanteExceptionWarning
     */
    protected function mapUnit(): ?CObservationValueUnit
    {
        if (!$units = $this->message->queryTextNode("OBX.6/CE.1", $this->OBX_node)) {
            return null;
        }

        // Retrieve Observation Unit
        $identifier    = $units;
        $coding_system =
            $this->message->queryTextNode("OBX.6/CE.3", $this->OBX_node) ?: CObservationValueUnit::SYSTEM_UNKNOWN;
        $text          = $this->message->queryTextNode("OBX.6/CE.2", $this->OBX_node);
        $unit_type     = new CObservationValueUnit();
        if (!$unit_type->loadMatch($identifier, $coding_system, $text ?: $identifier)) {
            throw $this->makeError('OBX', '22', '10.7');
        }

        return $unit_type;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    private function parseContent(string $content): string
    {
        $type = $this->message->getObservationType($this->OBX_node->parentNode);
        if ($type !== 'CE') {
            return $content;
        }

        $separator = $this->message->_ref_exchange_hpr->_event_message->message->componentSeparator;

        $exploded_content = explode($separator, $content, 3);

        return isset($exploded_content[1]) && $exploded_content[1] ? $exploded_content[1] : $exploded_content[0];
    }

    private function findOrCreateResponsible(): ?CObservationResponsibleObserver
    {
        if (!$validator_name = $this->message->queryTextNode("OBX.16/CE.2", $this->OBX_node)) {
            return null;
        }

        $responsible = new CObservationResponsibleObserver();
        $responsible->id = substr(sha1($validator_name), 0, 15);
        if (!$responsible->loadMatchingObject()) {
            $responsible->family_name = $validator_name;
            if ($msg = $responsible->store()) {
                $this->message->addError(
                    $this->makeError('OBX', '26', 'OBX.16', $msg)->getHprimError($this->message->_ref_exchange_hpr)
                );
            }
        }

        return $responsible->_id ? $responsible : null;
    }

    /**
     * @param CObservationResult $observation_result
     *
     * @return void
     * @throws Exception
     */
    private function mapResultExamen(CObservationResult $observation_result): void
    {
        $secteur_technique = $this->message->queryTextNode("OBX.15/CE.1", $this->OBX_node);
        $rang_secteur = $this->message->queryTextNode("OBX.15/CE.2", $this->OBX_node);
        if (!$secteur_technique && !$rang_secteur) {
            return;
        }

        $chapter = null;
        if (is_string($secteur_technique) && (is_numeric($rang_secteur) || !$rang_secteur)) {
            $chapter = $secteur_technique;
        } elseif (is_string($rang_secteur) && (is_numeric($secteur_technique) || !$secteur_technique)) {
            $chapter = $rang_secteur;
        }

        if (!$chapter) {
            return;
        }

        $result_examen = new CObservationResultExamen();
        $result_examen->observation_result_set_id = $this->observation_result_set->_id;
        $result_examen->code = $chapter;
        $result_examen->system = 'L';
        if (!$result_examen->loadMatchingObject()) {
            if ($msg = $result_examen->store()) {
                $this->message->addError(
                    $this->makeError('OBX', '23', '15', $msg)->getHprimError($this->message->_ref_exchange_hpr)
                );
            }
        }

        if ($result_examen->_id) {
            $observation_result->observation_result_examen_id = $result_examen->_id;
        }
    }

    /**
     * @param CObservationResult $result
     *
     * @return void
     * @throws Exception
     */
    protected function mapResultObservationAbNormal(CObservationResult $result): void
    {
        $flag_nodes = $this->message->query("OBX.8", $this->OBX_node);
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
        if (!$manager_ab_normal->synchronizeFlags($result, $flags)) {
            foreach ($manager_ab_normal->getErrorStoringProcess() as $msg_error) {
                $this->message->addError(
                    $this->makeError('OBX', '26', '8', $msg_error)->getHprimError($this->message->_ref_exchange_hpr)
                );
            }

            foreach ($manager_ab_normal->getErrorStoringProcess() as $msg_error) {
                $this->message->addError(
                    $this->makeError('OBX', '27', '8', $msg_error)->getHprimError($this->message->_ref_exchange_hpr)
                );
            }
        }
    }
}
