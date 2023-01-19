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
use Ox\Import\Framework\Entity\Sejour;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\PlanningOp\Import\OxPivotSejour;

/**
 * Sejour mapper for generic import
 */
class SejourMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'     => $this->getValue($row, OxPivotSejour::FIELD_ID),
            'nda'             => $this->getValue($row, OxPivotSejour::FIELD_NDA),
            'type'            => $this->getValue($row, OxPivotSejour::FIELD_TYPE),
            'entree_prevue'   => $this->getValue($row, OxPivotSejour::FIELD_ENTREE_PREVUE) ? $this->convertToDateTime(
                $row[OxPivotSejour::FIELD_ENTREE_PREVUE]
            ) : $this->convertToDateTime($row[OxPivotSejour::FIELD_ENTREE_REELLE]),
            'entree_reelle'   => $this->getValue($row, OxPivotSejour::FIELD_ENTREE_REELLE)
                ? $this->convertToDateTime($row[OxPivotSejour::FIELD_ENTREE_REELLE]) : null,
            'sortie_prevue'   => $this->getValue($row, OxPivotSejour::FIELD_SORTIE_PREVUE) ? $this->convertToDateTime(
                $row[OxPivotSejour::FIELD_SORTIE_PREVUE]
            ) : $this->convertToDateTime($row[OxPivotSejour::FIELD_SORTIE_REELLE]),
            'sortie_reelle'   => $this->getValue($row, OxPivotSejour::FIELD_SORTIE_REELLE)
                ? $this->convertToDateTime($row[OxPivotSejour::FIELD_SORTIE_REELLE]) : null,
            'libelle'         => $this->getValue($row, OxPivotSejour::FIELD_LIBELLE),
            'patient_id'      => $this->getValue($row, OxPivotSejour::FIELD_PATIENT),
            'praticien_id'    => $this->getValue($row, OxPivotSejour::FIELD_PRATICIEN),
            'prestation'      => $this->getValue($row, OxPivotSejour::FIELD_PRATICIEN),
            'mode_traitement' => CMbString::lower($this->getValue($row, OxPivotSejour::FIELD_MODE_TRAITEMENT)),
            'mode_entree'     => CMbString::lower($this->getValue($row, OxPivotSejour::FIELD_MODE_ENTREE)),
            'mode_sortie'     => CMbString::lower($this->getValue($row, OxPivotSejour::FIELD_MODE_SORTIE)),
        ];

        return Sejour::fromState($map);
    }
}
