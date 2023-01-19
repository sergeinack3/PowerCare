<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Exception;
use Ox\Core\CMbString;
use Ox\Import\Framework\Entity\Affectation;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Hospi\Import\OxPivotAffectation;

/**
 * Affectation mapper for generic import
 */
class AffectationMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id' => $this->getValue($row, OxPivotAffectation::FIELD_ID),
            'sejour_id'   => $this->getValue($row, OxPivotAffectation::FIELD_SEJOUR_ID),
            'nom_service' => CMbString::lower($this->getValue($row, OxPivotAffectation::FIELD_NOM_SERVICE)),
            'nom_lit'     => CMbString::lower($this->getValue($row, OxPivotAffectation::FIELD_NOM_LIT)),
            'entree'      => $this->getValue($row, OxPivotAffectation::FIELD_ENTREE)
                ? $this->convertToDateTime($row[OxPivotAffectation::FIELD_ENTREE]) : null,
            'sortie'      => $this->getValue($row, OxPivotAffectation::FIELD_SORTIE)
                ? $this->convertToDateTime($row[OxPivotAffectation::FIELD_SORTIE]) : null,
            'remarques'   => $this->getValue($row, OxPivotAffectation::FIELD_REMARQUES),
            'effectue'    => $this->getValue($row, OxPivotAffectation::FIELD_EFFECTUE),
            'mode_entree' => CMbString::lower($this->getValue($row, OxPivotAffectation::FIELD_MODE_ENTREE)),
            'mode_sortie' => CMbString::lower($this->getValue($row, OxPivotAffectation::FIELD_MODE_SORTIE)),
            'code_uf'     => CMbString::lower($this->getValue($row, OxPivotAffectation::FIELD_CODE_UF)),
        ];

        return Affectation::fromState($map);
    }
}
