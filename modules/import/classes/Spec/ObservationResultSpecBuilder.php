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
use Ox\Mediboard\ObservationResult\CObservationResult;

/**
 * Generic Import - OX Labo
 * ObservationResultSpecBuilder
 */
class ObservationResultSpecBuilder
{
    private const FIELD_ID                      = 'external_id';
    private const FIELD_OBSERVATION_RESULT_SET  = 'observation_result_set_id';
    private const FIELD_OBSERVATION_EXAM        = 'observation_exam_id';
    private const FIELD_OBSERVATION_RESPONSIBLE = 'observation_responsible_id';
    private const FIELD_OBSERVATION_IDENTIFIER  = 'identifier_id';
    private const FIELD_SUB_IDENTIFIER          = 'sub_identifier';
    private const FIELD_STATUS                  = 'status';
    private const FIELD_DATETIME                = 'datetime';
    private const FIELD_METHOD                  = 'method';

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
                   $this->buildSpecObservationResultSet(),
                   $this->buildSpecObservationIdentifier(),
                   $this->buildSpecStatus(),
                   $this->buildSpecObservationExam(),
                   $this->buildSpecObservationResponsible(),
                   $this->buildSpecSubIdentifier(),
                   $this->buildSpecDatetime(),
                   $this->buildSpecMethod(),
               ]
        );
    }

    /**
     * Spec of observation_result_set_id
     *
     * @return SpecificationInterface
     */
    private function buildSpecObservationResultSet(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_OBSERVATION_RESULT_SET),
            MaxLength::is(self::FIELD_OBSERVATION_RESULT_SET, 80)
        );
    }

    /**
     * Spec of observation_identifier_id
     *
     * @return SpecificationInterface
     */
    private function buildSpecObservationIdentifier(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_OBSERVATION_IDENTIFIER),
            MaxLength::is(self::FIELD_OBSERVATION_IDENTIFIER, 80)
        );
    }

    /**
     * Spec of status
     *
     * @return SpecificationInterface
     */
    private function buildSpecStatus(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_STATUS),
            Enum::is(
                self::FIELD_STATUS,
                [
                    CObservationResult::STATUS_FINAL,
                    CObservationResult::STATUS_PRELIMINARY,
                    CObservationResult::STATUS_CANCELED,
                    CObservationResult::STATUS_CORRECTED,
                    CObservationResult::STATUS_DELETED,
                ]
            )
        );
    }

    /**
     * Spec of observation_exam_id
     *
     * @return SpecificationInterface
     */
    private function buildSpecObservationExam(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_OBSERVATION_EXAM),
            MaxLength::is(self::FIELD_OBSERVATION_EXAM, 80)
        );
    }

    /**
     * Spec of observation_exam_id
     *
     * @return SpecificationInterface
     */
    private function buildSpecObservationResponsible(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_OBSERVATION_RESPONSIBLE),
            MaxLength::is(self::FIELD_OBSERVATION_RESPONSIBLE, 80)
        );
    }

    /**
     * Spec of sub_identifier
     *
     * @return SpecificationInterface
     */
    private function buildSpecSubIdentifier(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_SUB_IDENTIFIER),
            MaxLength::is(self::FIELD_SUB_IDENTIFIER, 255)
        );
    }

    /**
     * Spec of datetime
     *
     * @return SpecificationInterface
     */
    private function buildSpecDatetime(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_DATETIME),
            new AndX(
                LessThanOrEqual::is(self::FIELD_DATETIME, new DateTime()),
                GreaterThanOrEqual::is(self::FIELD_DATETIME, new DateTime('1850-01-01')),
                InstanceOfX::is(self::FIELD_DATETIME, DateTime::class)
            ),
        );
    }

    /**
     * Spec of method
     *
     * @return SpecificationInterface
     */
    private function buildSpecMethod(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_METHOD),
            MaxLength::is(self::FIELD_METHOD, 255)
        );
    }
}
