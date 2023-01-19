<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\Injection;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Cabinet\Import\OxPivotInjection;

/**
 * Injection mapper for generic import
 */
class InjectionMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'       => $this->getValue($row, OxPivotInjection::FIELD_ID),
            'patient_id'        => $this->getValue($row, OxPivotInjection::FIELD_PATIENT),
            'practitioner_name' => $this->getValue($row, OxPivotInjection::FIELD_PRACTITIONER_NAME),
            'injection_date'    => $this->getValue(
                $row,
                OxPivotInjection::FIELD_INJECTION_DATE
            ) ? $this->convertToDateTime($row[OxPivotInjection::FIELD_INJECTION_DATE]) : null,
            'batch'             => $this->getValue($row, OxPivotInjection::FIELD_BATCH),
            'speciality'        => $this->getValue($row, OxPivotInjection::FIELD_SPECIALITY),
            'remarques'         => $this->getValue($row, OxPivotInjection::FIELD_REMARQUES),
            'cip_product'       => $this->getValue($row, OxPivotInjection::FIELD_CIP_PRODUCT),
            'expiration_date'   => $this->getValue(
                $row,
                OxPivotInjection::FIELD_EXPIRATION_DATE
            ) ? $this->convertToDateTime($row[OxPivotInjection::FIELD_EXPIRATION_DATE]) : null,
            'recall_age'        => $this->getValue($row, OxPivotInjection::FIELD_RECALL_AGE),
            '_type_vaccin'      => $this->getValue($row, OxPivotInjection::FIELD_TYPE_VACCIN),
        ];

        return Injection::fromState($map);
    }
}
