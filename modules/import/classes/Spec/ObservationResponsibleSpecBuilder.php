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
 * ObservationResponsibleSpecBuilder
 */
class ObservationResponsibleSpecBuilder
{
    private const FIELD_ID         = 'external_id';
    private const FIELD_IDENTIFIER = 'identifier';
    private const FIELD_FIRSTNAME  = 'firstname';
    private const FIELD_LASTNAME   = 'lastname';

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
                   $this->buildSpecIdentifier(),
                   $this->buildSpecFirstname(),
                   $this->buildSpecLastname(),
               ]
        );
    }

    /**
     * Spec of identifier
     *
     * @return SpecificationInterface
     */
    private function buildSpecIdentifier(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_IDENTIFIER),
            MaxLength::is(self::FIELD_IDENTIFIER, 255)
        );
    }

    /**
     * Spec of firstname
     *
     * @return SpecificationInterface
     */
    private function buildSpecFirstname(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_FIRSTNAME),
            MaxLength::is(self::FIELD_FIRSTNAME, 255)
        );
    }

    /**
     * Spec of lastname
     *
     * @return SpecificationInterface
     */
    private function buildSpecLastname(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_LASTNAME),
            MaxLength::is(self::FIELD_LASTNAME, 255)
        );
    }
}
