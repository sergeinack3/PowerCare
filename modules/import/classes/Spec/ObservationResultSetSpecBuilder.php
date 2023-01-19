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
 * Generic Import - OX Labo
 * ObservationResultSetSpecBuilder
 */
class ObservationResultSetSpecBuilder
{
    private const  FIELD_ID                  = 'external_id';
    private const  FIELD_PATIENT_REPLACE     = 'patient_replace_id';
    private const  FIELD_DATETIME            = 'datetime';
    private const  FIELD_SENDER              = 'sender_id';
    private const  FIELD_SENDER_CLASS        = 'sender_class';
    private const  FIELD_PATIENT_IDENTIFIER  = 'patient_identifier';
    private const  FIELD_SEJOUR_IDENTIFIER   = 'sejour_identifier';
    private const  FIELD_LABO_NUMBER         = 'labo_number';
    private const  FIELD_LABO_REQUEST_NUMBER = 'labo_request_number';
    private const  FIELD_ACTIF               = 'actif';

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
                   $this->buildSpecPatientReplace(),
                   $this->buildSpecDatetime(),
                   $this->buildSpecSender(),
                   $this->buildSpecSenderClass(),
                   $this->buildSpecPatientIdentifier(),
                   $this->buildSpecLaboRequestNumber(),
                   $this->buildSpecSejourIdentifier(),
                   $this->buildSpecLaboNumber(),
                   $this->buildSpecIsActive(),
               ]
        );
    }

    /**
     * Spec of patient_replace_id
     *
     * @return SpecificationInterface
     */
    private function buildSpecPatientReplace(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_PATIENT_REPLACE),
            MaxLength::is(self::FIELD_PATIENT_REPLACE, 80)
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
     * Spec of sender_id
     *
     * @return SpecificationInterface
     */
    private function buildSpecSender(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_SENDER),
            MaxLength::is(self::FIELD_SENDER, 80)
        );
    }

    /**
     * Spec of sender_class
     *
     * @return SpecificationInterface
     */
    private function buildSpecSenderClass(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_SENDER_CLASS),
            MaxLength::is(self::FIELD_SENDER_CLASS, 255)
        );
    }

    /**
     * Spec of patient_identifier
     *
     * @return SpecificationInterface
     */
    private function buildSpecPatientIdentifier(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_PATIENT_IDENTIFIER),
            MaxLength::is(self::FIELD_PATIENT_IDENTIFIER, 255)
        );
    }

    /**
     * Spec of labo_request_number
     *
     * @return SpecificationInterface
     */
    private function buildSpecLaboRequestNumber(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_LABO_REQUEST_NUMBER),
            MaxLength::is(self::FIELD_LABO_REQUEST_NUMBER, 50)
        );
    }

    /**
     * Spec of sejour_identifier
     *
     * @return SpecificationInterface
     */
    private function buildSpecSejourIdentifier(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_SEJOUR_IDENTIFIER),
            MaxLength::is(self::FIELD_SEJOUR_IDENTIFIER, 255)
        );
    }

    /**
     * Spec of labo_number
     *
     * @return SpecificationInterface
     */
    private function buildSpecLaboNumber(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_LABO_NUMBER),
            MaxLength::is(self::FIELD_LABO_NUMBER, 50)
        );
    }

    /**
     * Spec of actif
     *
     * @return SpecificationInterface
     */
    private function buildSpecIsActive(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_ACTIF),
            Enum::is(
                self::FIELD_ACTIF,
                [
                    '0',
                    '1',
                ]
            ),
        );
    }
}
