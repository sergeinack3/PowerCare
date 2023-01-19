<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\Correspondant;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Patients\Import\OxPivotCorrespondant;

/**
 * Description
 */
class CorrespondantMedicalMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id' => $this->getValue($row, OxPivotCorrespondant::FIELD_ID),
            'medecin_id'  => $this->getValue($row, OxPivotCorrespondant::FIELD_MEDECIN),
            'patient_id'  => $this->getValue($row, OxPivotCorrespondant::FIELD_PATIENT),
        ];

        return Correspondant::fromState($map);
    }
}
