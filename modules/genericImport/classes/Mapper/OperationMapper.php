<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Core\CMbString;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\Operation;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\PlanningOp\Import\OxPivotOperation;

/**
 * Operation mapper for generic import
 */
class OperationMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id' => $this->getValue($row, OxPivotOperation::FIELD_ID),
            'sejour_id'   => $this->getValue($row, OxPivotOperation::FIELD_SEJOUR_ID),
            'chir_id'     => $this->getValue($row, OxPivotOperation::FIELD_CHIR_ID),
            'cote'        => CMbString::lower($this->getValue($row, OxPivotOperation::FIELD_COTE)),
            'date_time'   => $this->getValue($row, OxPivotOperation::FIELD_DATE_TIME),
            'libelle'     => $this->getValue($row, OxPivotOperation::FIELD_LIBELLE),
            'examen'      => $this->getValue($row, OxPivotOperation::FIELD_EXAMEN),
        ];

        return Operation::fromState($map);
    }
}
