<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\Traitement;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Patients\Import\OxPivotTraitement;

/**
 * Description
 */
class TraitementMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id' => $this->getValue($row, OxPivotTraitement::FIELD_ID),
            'debut'       => $this->getValue($row, OxPivotTraitement::FIELD_DATE_DEBUT)
                ? $this->convertToDateTime($row[OxPivotTraitement::FIELD_DATE_DEBUT]) : null,
            'fin'         => $this->getValue($row, OxPivotTraitement::FIELD_DATE_FIN)
                ? $this->convertToDateTime($row[OxPivotTraitement::FIELD_DATE_FIN]) : null,
            'traitement'  => $this->getValue($row, OxPivotTraitement::FIELD_TEXT),
            'patient_id'  => $this->getValue($row, OxPivotTraitement::FIELD_PATIENT),
            'owner_id'    => $this->getValue($row, OxPivotTraitement::FIELD_PRATICIEN),
        ];

        return Traitement::fromState($map);
    }
}
