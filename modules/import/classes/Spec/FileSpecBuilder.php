<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use DateTime;
use Ox\Core\Specification\AndX;
use Ox\Core\Specification\InstanceOfX;
use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationInterface;

/**
 * Description
 */
class FileSpecBuilder
{
    private const FIELD_ID        = 'external_id';
    private const FIELD_FILE_NAME = 'file_name';
    private const FIELD_DATE      = 'file_date';
    private const FIELD_TYPE      = 'file_type';
    private const FIELD_AUTHOR    = 'author_id';


    public function build(): ?SpecificationInterface
    {
        $spec_to_add = [
            $this->buildSpec(self::FIELD_ID),
            $this->buildSpec(self::FIELD_FILE_NAME),
            $this->buildSpec(self::FIELD_DATE),
            //$this->buildSpec(self::FIELD_AUTHOR),
        ];

        if ($spec = $this->buildSpec(self::FIELD_TYPE)) {
            $spec_to_add[] = $spec;
        }

        return new AndX(...$spec_to_add);
    }

    /**
     * Build spec depending on $spec_name
     *
     * @param string $spec_name
     *
     * @return SpecificationInterface|null
     */
    public function buildSpec(string $spec_name): ?SpecificationInterface
    {
        switch ($spec_name) {
            case self::FIELD_ID:
            case self::FIELD_AUTHOR:
                return $this->getNotNullSpec($spec_name);

            case self::FIELD_FILE_NAME:
                return $this->getFileNameSpec();

            case self::FIELD_DATE:
                return $this->getDateSpec();

            case self::FIELD_TYPE:
                return $this->getTypeSpec();

            default:
                return null;
        }
    }

    /**
     * @param string $field_name
     *
     * @return NotNull
     */
    private function getNotNullSpec(string $field_name): NotNull
    {
        return NotNull::is($field_name);
    }

    private function getFileNameSpec(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_FILE_NAME),
            MaxLength::is(self::FIELD_FILE_NAME, 255)
        );
    }

    private function getDateSpec(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_DATE),
            InstanceOfX::is(self::FIELD_DATE, DateTime::class)
        );
    }

    private function getTypeSpec(): OrX
    {
        return new OrX(
            MaxLength::is(self::FIELD_TYPE, 255),
            IsNull::is(self::FIELD_TYPE)
        );
    }
}
