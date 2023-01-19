<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\Constante;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Patients\Import\OxPivotConstante;

/**
 * Map constante from generic import format to Constant Object
 */
class ConstanteMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'        => $this->getValue($row, OxPivotConstante::FIELD_ID),
            'user_id'            => $this->getValue($row, OxPivotConstante::FIELD_PRATICIEN),
            'patient_id'         => $this->getValue($row, OxPivotConstante::FIELD_PATIENT),
            'datetime'           => $this->getValue($row, OxPivotConstante::FIELD_DATE)
                ? $this->convertToDateTime($row[OxPivotConstante::FIELD_DATE]) : null,
            'taille'             => $this->getValue($row, OxPivotConstante::FIELD_TAILLE),
            'poids'              => $this->getValue($row, OxPivotConstante::FIELD_POIDS),
            'pouls'              => $this->getValue($row, OxPivotConstante::FIELD_PULSE),
            'temperature'        => $this->getValue($row, OxPivotConstante::FIELD_TEMPERATURE),
            'ta_droit_systole'   => $this->getValue($row, OxPivotConstante::FIELD_BLOOD_PRESSURE_SYSTOLE_RIGHT),
            'ta_droit_diastole'  => $this->getValue($row, OxPivotConstante::FIELD_BLOOD_PRESSURE_DIASTOLE_RIGHT),
            'ta_gauche_systole'  => $this->getValue($row, OxPivotConstante::FIELD_BLOOD_PRESSURE_SYSTOLE_LEFT),
            'ta_gauche_diastole' => $this->getValue($row, OxPivotConstante::FIELD_BLOOD_PRESSURE_DIASTOLE_LEFT),
            'pointure'           => $this->getValue($row, OxPivotConstante::FIELD_SHOE_SIZE)
        ];

        return Constante::fromState($map);
    }
}
