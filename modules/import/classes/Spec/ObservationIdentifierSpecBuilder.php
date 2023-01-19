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
 * ObservationIdentifierSpecBuilder
 */
class ObservationIdentifierSpecBuilder
{
    private const FIELD_ID                = 'external_id';
    private const FIELD_IDENTIFIER        = 'identifier';
    private const FIELD_TEXT              = 'text';
    private const FIELD_CODING_SYSTEM     = 'coding_system';
    private const FIELD_ALT_IDENTIFIER    = 'alt_identifier';
    private const FIELD_ALT_TEXT          = 'alt_text';
    private const FIELD_ALT_CODING_SYSTEM = 'alt_coding_system';

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
                   $this->buildSpecText(),
                   $this->buildSpecCodingSystem(),
                   $this->buildSpecAltIdentifier(),
                   $this->buildSpecAltText(),
                   $this->buildSpecAltCodingSystem(),
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
     * Spec of text
     *
     * @return SpecificationInterface
     */
    private function buildSpecText(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_TEXT),
            MaxLength::is(self::FIELD_TEXT, 255)
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
            MaxLength::is(self::FIELD_CODING_SYSTEM, 255)
        );
    }

    /**
     * Spec of alt_identifier
     *
     * @return SpecificationInterface
     */
    private function buildSpecAltIdentifier(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_ALT_IDENTIFIER),
            MaxLength::is(self::FIELD_ALT_IDENTIFIER, 255)
        );
    }

    /**
     * Spec of alt_text
     *
     * @return SpecificationInterface
     */
    private function buildSpecAltText(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_ALT_TEXT),
            MaxLength::is(self::FIELD_ALT_TEXT, 255)
        );
    }

    /**
     * Spec of alt_coding_system
     *
     * @return SpecificationInterface
     */
    private function buildSpecAltCodingSystem(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_ALT_CODING_SYSTEM),
            MaxLength::is(self::FIELD_ALT_CODING_SYSTEM, 255)
        );
    }
}
