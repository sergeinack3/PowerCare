<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use Ox\Core\Specification\AndX;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\SpecificationInterface;

/**
 * Generic Import - OX Labo
 * ObservationValueUnitSpecBuilder
 */
class ObservationValueUnitSpecBuilder
{
    private const FIELD_ID            = 'external_id';
    private const FIELD_CODE          = 'code';
    private const FIELD_LABEL         = 'label';
    private const FIELD_CODING_SYSTEM = 'coding_system';

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
                   $this->buildSpecCode(),
                   $this->buildSpecLabel(),
                   $this->buildSpecCodingSystem(),
               ]
        );
    }

    /**
     * Spec of code
     *
     * @return SpecificationInterface
     */
    private function buildSpecCode(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_CODE),
            MaxLength::is(self::FIELD_CODE, 40)
        );
    }

    /**
     * Spec of label
     *
     * @return SpecificationInterface
     */
    private function buildSpecLabel(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_LABEL),
            MaxLength::is(self::FIELD_LABEL, 255)
        );
    }

    /**
     * Spec of coding_system
     *
     * @return SpecificationInterface
     */
    private function buildSpecCodingSystem(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_CODING_SYSTEM),
            MaxLength::is(self::FIELD_CODING_SYSTEM, 40)
        );
    }
}
