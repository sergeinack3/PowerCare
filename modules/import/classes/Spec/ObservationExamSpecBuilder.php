<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use DateTime;
use Ox\Core\Specification\AndX;
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
 * ObservationExamSpecBuilder
 */
class ObservationExamSpecBuilder
{
    private const FIELD_ID                     = 'external_id';
    private const FIELD_OBSERVATION_RESULT_SET = 'observation_result_set_id';
    private const FIELD_CODE                   = 'code';
    private const FIELD_SYSTEM                 = 'coding_system';
    private const FIELD_DATETIME               = 'datetime';
    private const FIELD_LOINC_CHAPTER          = 'loinc_chapter';
    private const FIELD_LOINC_SUBCHAPTER       = 'loinc_subchapter';

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
                   $this->buildSpecCode(),
                   $this->buildSpecSystem(),
                   $this->buildSpecDatetime(),
                   $this->buildSpecLoincChapter(),
                   $this->buildSpecLoincSubchapter(),
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
     * Spec of coding_system
     *
     * @return SpecificationInterface
     */
    private function buildSpecSystem(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_SYSTEM),
            MaxLength::is(self::FIELD_SYSTEM, 40)
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
            )
        );
    }

    /**
     * Spec of loinc_chapter
     *
     * @return SpecificationInterface
     */
    private function buildSpecLoincChapter(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_LOINC_CHAPTER),
            MaxLength::is(self::FIELD_LOINC_CHAPTER, 255)
        );
    }

    /**
     * Spec of loinc_subchapter
     *
     * @return SpecificationInterface
     */
    private function buildSpecLoincSubchapter(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_LOINC_SUBCHAPTER),
            MaxLength::is(self::FIELD_LOINC_SUBCHAPTER, 255)
        );
    }
}
