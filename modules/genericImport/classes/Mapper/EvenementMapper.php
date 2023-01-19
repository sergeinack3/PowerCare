<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Exception;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\EvenementPatient;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Patients\Import\OxPivotEvenementPatient;

/**
 * Mapper for EvenementMapper
 */
class EvenementMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'     => $this->getValue($row, OxPivotEvenementPatient::FIELD_ID),
            'patient_id'      => $this->getValue($row, OxPivotEvenementPatient::FIELD_PATIENT),
            'practitioner_id' => $this->getValue($row, OxPivotEvenementPatient::FIELD_PRACTITIONER),
            'datetime'        => $this->convertToDateTime(
                $this->getValue($row, OxPivotEvenementPatient::FIELD_DATETIME)
            ),
            'label'           => $this->getValue($row, OxPivotEvenementPatient::FIELD_LABEL),
            'type'            => $this->getValue($row, OxPivotEvenementPatient::FIELD_TYPE),
            'description'     => $this->getValue($row, OxPivotEvenementPatient::FIELD_DESCRIPTION),
        ];

        return EvenementPatient::fromState($map);
    }
}
