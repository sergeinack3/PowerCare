<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\ActeCCAM;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\SalleOp\Import\OxPivotActeCCAM;

/**
 * Description
 */
class ActeCCAMMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'         => $this->getValue($row, OxPivotActeCCAM::FIELD_ID),
            'executant_id'        => $this->getValue($row, OxPivotActeCCAM::FIELD_EXECUTANT),
            'consultation_id'     => $this->getValue($row, OxPivotActeCCAM::FIELD_CONSULTATION),
            'code_acte'           => $this->getValue($row, OxPivotActeCCAM::FIELD_CODE_ACTE),
            'date_execution'      => $this->getValue($row, OxPivotActeCCAM::FIELD_DATE_EXECUTION)
                ? $this->convertToDateTime($row[OxPivotActeCCAM::FIELD_DATE_EXECUTION]) : null,
            'code_activite'       => $this->getValue($row, OxPivotActeCCAM::FIELD_CODE_ACTIVITE),
            'code_phase'          => $this->getValue($row, OxPivotActeCCAM::FIELD_CODE_PHASE),
            'modificateurs'       => $this->getValue($row, OxPivotActeCCAM::FIELD_MODIFICATEURS),
            'montant_base'        => $this->getValue($row, OxPivotActeCCAM::FIELD_MONTANT_BASE),
            'montant_depassement' => $this->getValue($row, OxPivotActeCCAM::FIELD_MONTANT_DEPASSEMENT),
        ];

        return ActeCCAM::fromState($map);
    }
}
