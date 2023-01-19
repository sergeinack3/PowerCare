<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use DateTime;
use Ox\Core\Specification\AndX;
use Ox\Core\Specification\Enum;
use Ox\Core\Specification\GreaterThanOrEqual;
use Ox\Core\Specification\InstanceOfX;
use Ox\Core\Specification\LessThanOrEqual;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\SpecificationInterface;

/**
 * Generic Import - OX Labo
 * ObservationPatientSpecBuilder
 */
class ObservationPatientSpecBuilder
{
    private const FIELD_ID        = 'external_id';
    private const FIELD_FIRSTNAME = 'firstname';
    private const FIELD_LASTNAME  = 'lastname';
    private const FIELD_BIRTHNAME = 'birthname';
    private const FIELD_BIRTHDATE = 'birthdate';
    private const FIELD_GENDER    = 'gender';
    private const FIELD_IPP       = 'ipp';

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
                   $this->buildSpecFirstname(),
                   $this->buildSpecLastname(),
                   $this->buildSpecBirthname(),
                   $this->buildSpecBirthdate(),
                   $this->buildSpecGender(),
                   $this->buildSpecIPP(),
               ]
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

    /**
     * Spec of birthname
     *
     * @return SpecificationInterface
     */
    private function buildSpecBirthname(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_BIRTHNAME),
            MaxLength::is(self::FIELD_BIRTHNAME, 255)
        );
    }

    /**
     * Spec of birthdate
     *
     * @return SpecificationInterface
     */
    private function buildSpecBirthdate(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_BIRTHDATE),
            LessThanOrEqual::is(self::FIELD_BIRTHDATE, new DateTime()),
            GreaterThanOrEqual::is(self::FIELD_BIRTHDATE, new DateTime('1850-01-01')),
            InstanceOfX::is(self::FIELD_BIRTHDATE, DateTime::class)
        );
    }

    /**
     * Spec of gender
     *
     * @return SpecificationInterface
     */
    private function buildSpecGender(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_GENDER),
            Enum::is(
                self::FIELD_GENDER,
                [
                    'm',
                    'f'
                ]
            )
        );
    }

    /**
     * Spec of ipp
     *
     * @return SpecificationInterface
     */
    private function buildSpecIPP(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_IPP),
            MaxLength::is(self::FIELD_IPP, 255)
        );
    }
}
