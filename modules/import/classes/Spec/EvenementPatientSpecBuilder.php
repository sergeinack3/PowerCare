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
use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\LessThanOrEqual;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationInterface;

/**
 * Generic Import - Generic
 * EvenementPatientSpecBuilder
 */
class EvenementPatientSpecBuilder
{
    private const FIELD_ID           = 'external_id';
    private const FIELD_PATIENT      = 'patient_id';
    private const FIELD_PRACTITIONER = 'practitioner_id';
    private const FIELD_DATETIME     = 'datetime';
    private const FIELD_LABEL        = 'label';
    private const FIELD_TYPE         = 'type';
    private const FIELD_DESCRIPTION  = 'description';

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
                   $this->buildSpecPatient(),
                   $this->buildSpecPractitioner(),
                   $this->buildSpecDatetime(),
                   $this->buildSpecLabel(),
                   $this->buildSpecType(),
                   $this->buildSpecDescription()
               ]
        );
    }

    /**
     * Spec of patient_id
     *
     * @return SpecificationInterface
     */
    private function buildSpecPatient(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_PATIENT),
            MaxLength::is(self::FIELD_PATIENT, 80)
        );
    }

    /**
     * Spec of practitioner_id
     *
     * @return SpecificationInterface
     */
    private function buildSpecPractitioner(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_PRACTITIONER),
            MaxLength::is(self::FIELD_PRACTITIONER, 80)
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
     * Spec of datetime
     *
     * @return SpecificationInterface
     */
    private function buildSpecDatetime(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_DATETIME),
            LessThanOrEqual::is(self::FIELD_DATETIME, new DateTime()),
            GreaterThanOrEqual::is(self::FIELD_DATETIME, new DateTime('1850-01-01')),
            InstanceOfX::is(self::FIELD_DATETIME, DateTime::class)
        );
    }

    /**
     * Spec of type
     *
     * @return SpecificationInterface
     */
    private function buildSpecType(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_TYPE),
            new AndX(
                MaxLength::is(self::FIELD_TYPE, 80),
                Enum::is(
                    self::FIELD_TYPE,
                    [
                        'sejour',
                        'intervention',
                        'evt'
                    ]
                )
            )
        );
    }

    /**
     * Spec of description
     *
     * @return SpecificationInterface
     */
    private function buildSpecDescription(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_DESCRIPTION),
            MaxLength::is(self::FIELD_DESCRIPTION, 65535)
        );
    }
}
