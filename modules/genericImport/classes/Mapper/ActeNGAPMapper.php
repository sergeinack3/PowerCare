<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\ActeNGAP;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Cabinet\Import\OxPivotActeNGAP;

/**
 * Description
 */
class ActeNGAPMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'         => $this->getValue($row, OxPivotActeNGAP::FIELD_ID),
            'executant_id'        => $this->getValue($row, OxPivotActeNGAP::FIELD_EXECUTANT),
            'consultation_id'     => $this->getValue($row, OxPivotActeNGAP::FIELD_CONSULTATION),
            'code_acte'           => $this->getValue($row, OxPivotActeNGAP::FIELD_CODE_ACTE),
            'date_execution'      => $this->getValue($row, OxPivotActeNGAP::FIELD_DATE_EXECUTION)
                ? $this->convertToDateTime($row[OxPivotActeNGAP::FIELD_DATE_EXECUTION]) : null,
            'quantite'            => $this->getValue($row, OxPivotActeNGAP::FIELD_QUANTITE),
            'coefficient'         => $this->getValue($row, OxPivotActeNGAP::FIELD_COEFFICIENT),
            'numero_dent'         => $this->getValue($row, OxPivotActeNGAP::FIELD_NUMERO_DENT),
            'montant_base'        => $this->getValue($row, OxPivotActeNGAP::FIELD_MONTANT_BASE),
            'montant_depassement' => $this->getValue($row, OxPivotActeNGAP::FIELD_MONTANT_DEPASSEMENT),
        ];

        return ActeNGAP::fromState($map);
    }
}
