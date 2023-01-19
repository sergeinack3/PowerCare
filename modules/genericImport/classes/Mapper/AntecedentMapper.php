<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\Antecedent;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Patients\Import\OxPivotAntecedent;

/**
 * Description
 */
class AntecedentMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id' => $this->getValue($row, OxPivotAntecedent::FIELD_ID),
            'owner_id'    => $this->getValue($row, OxPivotAntecedent::FIELD_PRATICIEN),
            'patient_id'  => $this->getValue($row, OxPivotAntecedent::FIELD_PATIENT),
            'text'        => $this->getValue($row, OxPivotAntecedent::FIELD_TEXT),
            'comment'     => $this->getValue($row, OxPivotAntecedent::FIELD_COMMENT),
            'date'        => $this->getValue($row, OxPivotAntecedent::FIELD_DATE)
                ? $this->convertToDateTime($row[OxPivotAntecedent::FIELD_DATE]) : null,
            'type'        => $this->getValue($row, OxPivotAntecedent::FIELD_TYPE),
            'appareil'    => $this->getValue($row, OxPivotAntecedent::FIELD_APPAREIL),
        ];

        return Antecedent::fromState($map);
    }
}
