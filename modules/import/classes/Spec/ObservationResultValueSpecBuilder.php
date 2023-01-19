<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use Ox\Core\Specification\AndX;
use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationInterface;

/**
 * Generic Import - OX Labo
 * ObservationResultValueSpecBuilder
 */
class ObservationResultValueSpecBuilder
{
    private const FIELD_ID                 = 'external_id';
    private const FIELD_OBSERVATION_RESULT = 'observation_result_id';
    private const FIELD_VALUE              = 'value';
    private const FIELD_UNIT               = 'unit_id';
    private const FIELD_REFERENCE_RANGE    = 'reference_range';
    private const FIELD_RANK               = 'rank';

    /**
     * Builder
     *
     * @return SpecificationInterface
     */
    public function build(): SpecificationInterface
    {
        return new AndX(
            ...[
                   NotNull::is(self::FIELD_ID),
                   $this->buildSpecObservationResult(),
                   $this->buildSpecValue(),
                   $this->buildSpecUnit(),
                   $this->buildSpecReferenceRange(),
                   $this->buildSpecRank(),
               ]
        );
    }

    /**
     * Spec of observation_result_id
     *
     * @return SpecificationInterface
     */
    private function buildSpecObservationResult(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_OBSERVATION_RESULT),
            MaxLength::is(self::FIELD_OBSERVATION_RESULT, 80)
        );
    }

    /**
     * Spec of value
     *
     * @return SpecificationInterface
     */
    private function buildSpecValue(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_VALUE),
            MaxLength::is(self::FIELD_VALUE, 255)
        );
    }

    /**
     * Spec of unit_id
     *
     * @return SpecificationInterface
     */
    private function buildSpecUnit(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_UNIT),
            MaxLength::is(self::FIELD_UNIT, 80)
        );
    }

    /**
     * Spec of reference_range
     *
     * @return SpecificationInterface
     */
    private function buildSpecReferenceRange(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_REFERENCE_RANGE),
            MaxLength::is(self::FIELD_REFERENCE_RANGE, 255)
        );
    }

    /**
     * Spec of rank
     *
     * @return SpecificationInterface
     */
    private function buildSpecRank(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_RANK),
            MaxLength::is(self::FIELD_RANK, 11)
        );
    }
}
