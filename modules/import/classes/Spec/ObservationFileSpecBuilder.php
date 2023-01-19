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
 * ObservationFileSpecBuilder
 */
class ObservationFileSpecBuilder
{
    private const FIELD_ID                     = 'external_id';
    private const FIELD_OBSERVATION_RESULT_SET = 'observation_result_set_id';
    private const FIELD_FILE_NAME              = 'file_name';
    private const FIELD_FILE_TYPE              = 'file_type';
    private const FIELD_FILE_PATH              = 'file_path';

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
                   $this->buildSpecFileName(),
                   $this->buildSpecFileType(),
                   $this->buildSpecFilePath(),
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
     * Spec of file_name
     *
     * @return SpecificationInterface
     */
    private function buildSpecFileName(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_FILE_NAME),
            MaxLength::is(self::FIELD_FILE_NAME, 255)
        );
    }

    /**
     * Spec of file_type
     *
     * @return SpecificationInterface
     */
    private function buildSpecFileType(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_FILE_TYPE),
            MaxLength::is(self::FIELD_FILE_TYPE, 80)
        );
    }

    /**
     * Spec of file_path
     *
     * @return SpecificationInterface
     */
    private function buildSpecFilePath(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_FILE_PATH),
            MaxLength::is(self::FIELD_FILE_PATH, 255)
        );
    }
}
