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
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\ObservationResult\Interop\ObservationResultSetLocator;

class HandleObservationLabo extends HandleObservation
{
    /** @var CObservationResultSet */
    protected $observation_result_set;

    /**
     * @param DOMNode $OBR_node
     *
     * @return array
     * @throws CMbException
     */
    protected function handleOBR(DOMNode $OBR_node): array
    {
        if (!$this->getPatient()) {
            return [];
        }

        $this->observation_result_set = $this->findOrCreateObservationResultSet($OBR_node);

        return [
            self::KEY_OBR_NODE => $OBR_node
        ];
    }

    /**
     * Get object for handle observation
     *
     * @param DOMNode $OBX_node
     * @return HandleObservationResult
     */
    protected function getObjectResultHandle(DOMNode $OBX_node): HandleObservationResult
    {
        $type = $this->message->getObservationType($OBX_node->parentNode);
        switch ($type) {
            case "FIC":
            case "PDF":
            case "TIF":
                return new HandleObservationResultFiles($this->message);

            default:
                return new HandleObservationResultLabo($this->message);
        }
    }

    /**
     * @param array $params
     * @return array
     */
    protected function getParameters(array $params): array
    {
        $params[self::KEY_OBSERVATION_RESULT_SET] = $this->observation_result_set;

        return parent::getParameters($params);
    }

    /**
     * Retrieve CObservationResultSet if exist or create it
     * This Observation result set was tag by a CIdsante400
     *
     * @param DOMNode $OBR_node
     *
     * @return CObservationResultSet
     * @throws CMbException
     * @throws Exception
     */
    protected function findOrCreateObservationResultSet(DOMNode $OBR_node): CObservationResultSet
    {
        if (!$identifier = $this->message->queryTextNode('OBR.3/CM.2', $OBR_node)) {
            $identifier = $this->message->queryTextNode('OBR.3/CM.1', $OBR_node);
        }

        if (!$identifier) {
            throw $this->makeImportantError('OBR', '19', '9.3');
        }

        try {
            if ($datetime = $this->message->queryTextNode('OBR.7', $OBR_node)) {
                $datetime = CMbDT::dateTime($datetime);
            }

            $locator = (new ObservationResultSetLocator($identifier, $this->getSender(), $this->getPatient()))
                ->setTarget($this->getTarget())
                ->setIdentifierSejour($this->message->identifier_sejour["sejour_identifier"])
                ->setDatetime($datetime);

            return $locator->findOrCreate();
        } catch (CMbException $exception) {
            throw $this->makeImportantError('OBR', '20', '9.4', $exception->getMessage());
        }
    }
}
