<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use Ox\Core\Specification\AndX;
use Ox\Core\Specification\Enum;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\SpecificationInterface;
use Ox\Mediboard\ObservationResult\CObservationAbnormalFlag;

/**
 * Generic Import - OX Labo
 * ObservationAbnormalFlagSpecBuilder
 */
class ObservationAbnormalFlagSpecBuilder
{
    private const FIELD_ID                 = 'external_id';
    private const FIELD_OBSERVATION_RESULT = 'observation_result_id';
    private const FIELD_FLAG               = 'flag';

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
                   $this->buildSpecFlag(),
               ]
        );
    }

    /**
     * Spec of observation_result
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
     * Spec of flag
     *
     * @return SpecificationInterface
     */
    private function buildSpecFlag(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_FLAG),
            MaxLength::is(self::FIELD_FLAG, 2),
            Enum::is(self::FIELD_FLAG, CObservationAbnormalFlag::$flags)
        );
    }
}
