<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use Exception;
use Ox\Core\Specification\AndX;
use Ox\Core\Specification\Enum;
use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\MinLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationInterface;

/**
 * External user spec builder
 */
class UserSpecBuilder
{
    use SpecBuilderTrait;

    private const FIELD_ID         = 'external_id';
    private const FIELD_USERNAME   = 'username';
    private const FIELD_FIRST_NAME = 'first_name';
    private const FIELD_LAST_NAME  = 'last_name';
    private const FIELD_GENDER     = 'gender';
    private const FIELD_BIRTHDAY   = 'birthday';
    private const FIELD_EMAIL      = 'email';
    private const FIELD_PHONE      = 'phone';
    private const FIELD_MOBILE     = 'mobile';
    private const FIELD_ADDRESS    = 'address';
    private const FIELD_CITY       = 'city';
    private const FIELD_ZIP        = 'zip';
    private const FIELD_COUNTRY    = 'country';

    /**
     * @return SpecificationInterface|null
     * @throws Exception
     */
    public function build(): ?SpecificationInterface
    {
        $specs_to_add = [];

        $specs_to_add[] = $this->buildSpec(self::FIELD_ID);
        $specs_to_add[] = $this->buildSpec(self::FIELD_USERNAME);
        $specs_to_add[] = $this->buildSpec(self::FIELD_LAST_NAME);

        if ($spec = $this->buildSpec(self::FIELD_FIRST_NAME)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_GENDER)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_BIRTHDAY)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_EMAIL)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_PHONE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_MOBILE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_ADDRESS)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_CITY)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_ZIP)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_COUNTRY)) {
            $specs_to_add[] = $spec;
        }

        return new AndX(...$specs_to_add);
    }

    /**
     * Build spec depending on $spec_name
     *
     * @param string $spec_name
     *
     * @return SpecificationInterface|null
     * @throws Exception
     */
    private function buildSpec(string $spec_name): ?SpecificationInterface
    {
        switch ($spec_name) {
            case self::FIELD_ID:
                return $this->getNotNullSpec($spec_name);

            case self::FIELD_USERNAME:
                return $this->getUsernameSpec();

            case self::FIELD_FIRST_NAME:
                return $this->getFirstNameSpec();

            case self::FIELD_LAST_NAME:
                return $this->getLastNameSpec();

            case self::FIELD_GENDER:
                return $this->getGenderSpec();

            case self::FIELD_BIRTHDAY:
                return $this->getNaissanceSpec(self::FIELD_BIRTHDAY);

            case self::FIELD_EMAIL:
                return $this->getEmailSpec(self::FIELD_EMAIL);

            case self::FIELD_PHONE:
            case self::FIELD_MOBILE:
                return $this->getTelSpec($spec_name);

            case self::FIELD_ADDRESS:
                return $this->getAddressSpec();

            case self::FIELD_CITY:
            case self::FIELD_COUNTRY:
                return $this->getCitySpec($spec_name);

            case self::FIELD_ZIP:
                return $this->getZipSpec();

            default:
                return null;
        }
    }


    /**
     * Build a not null spec
     *
     * @return NotNull
     */
    private function getNotNullSpec(string $field_name): NotNull
    {
        return NotNull::is($field_name);
    }

    private function getUsernameSpec(): AndX
    {
        return new AndX(
            MaxLength::is(self::FIELD_USERNAME, 80),
            NotNull::is(self::FIELD_USERNAME)
        );
    }

    private function getFirstNameSpec(): MaxLength
    {
        return MaxLength::is(self::FIELD_FIRST_NAME, 50);
    }

    private function getLastNameSpec(): AndX
    {
        return new AndX(
            MaxLength::is(self::FIELD_LAST_NAME, 50),
            NotNull::is(self::FIELD_LAST_NAME)
        );
    }

    private function getGenderSpec(): OrX
    {
        return new OrX(
            Enum::is(self::FIELD_GENDER, ['m', 'f', 'u']),
            IsNull::is(self::FIELD_GENDER)
        );
    }

    private function getAddressSpec(): MaxLength
    {
        return MaxLength::is(self::FIELD_ADDRESS, 255);
    }

    private function getCitySpec(string $field_name): MaxLength
    {
        return MaxLength::is($field_name, 30);
    }

    private function getZipSpec(): OrX
    {
        return new OrX(
            new AndX(
                MaxLength::is(self::FIELD_ZIP, 5),
                MinLength::is(self::FIELD_ZIP, 5)
            ),
            IsNull::is(self::FIELD_ZIP)
        );
    }
}
