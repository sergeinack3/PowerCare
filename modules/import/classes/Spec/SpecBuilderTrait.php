<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use DateTime;
use Ox\Core\CAppUI;
use Ox\Core\Specification\AndX;
use Ox\Core\Specification\Enum;
use Ox\Core\Specification\GreaterThanOrEqual;
use Ox\Core\Specification\InstanceOfX;
use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\LessThanOrEqual;
use Ox\Core\Specification\SpecMatch;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\MinLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationInterface;

/**
 * Specs builder for import
 */
trait SpecBuilderTrait
{
    /**
     * @param string $field_name
     *
     * @return SpecificationInterface
     */
    public function getStringSpec(string $field_name): SpecificationInterface
    {
        return MaxLength::is($field_name, 255);
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface
     */
    public function getNotNullSpec(string $field_name): SpecificationInterface
    {
        return NotNull::is($field_name);
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface
     */
    public function getStringNotNullSpec(string $field_name): SpecificationInterface
    {
        return new AndX(
            NotNull::is($field_name),
            MaxLength::is($field_name, 255)
        );
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface
     */
    public function getProfessionSpec(string $field_name): SpecificationInterface
    {
        return new OrX(
            MaxLength::is($field_name, 255),
            IsNull::is($field_name)
        );
    }

    /**
     * @param string $field_name
     * @param bool   $mandatory
     *
     * @return SpecificationInterface
     */
    public function getNameSpec(string $field_name, bool $mandatory = false): SpecificationInterface
    {
        if ($mandatory) {
            return new AndX(
                NotNull::is($field_name),
                MaxLength::is($field_name, 255)
            );
        }

        return new AndX(
            MaxLength::is($field_name, 255)
        );
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface
     */
    public function getEmailSpec(string $field_name): SpecificationInterface
    {
        return new OrX(
            IsNull::is($field_name),
            new AndX(
                SpecMatch::is($field_name, '/^[-a-z0-9\._\+]+@[-a-z0-9\.]+\.[a-z]{2,4}$/i'),
                MaxLength::is($field_name, 255)
            )
        );
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface|null
     */
    public function getAdresseSpec(string $field_name): ?SpecificationInterface
    {
        return (CAppUI::gconf(self::CONF_ADRESSE)) ? NotNull::is($field_name) : null;
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface|null
     */
    public function getVilleSpec(string $field_name): ?SpecificationInterface
    {
        if (CAppUI::gconf(self::CONF_ADRESSE)) {
            return new AndX(
                NotNull::is($field_name),
                MaxLength::is($field_name, 80)
            );
        }

        return null;
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface
     */
    public function getTelSpec(string $field_name): SpecificationInterface
    {
        $tel_spec = SpecMatch::is($field_name, '/^\d?(\d{2}[\s\.\-]?){5}$/');

        return new OrX(isNull::is($field_name), $tel_spec);
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface
     */
    private function getCpSpec(string $field_name): SpecificationInterface
    {
        //        [$min_cp, $max_cp] = CPatient::getLimitCharCP();
        //
        //        // Do not check num because of 2A and 2B
        //        $spec_max = MaxLength::is($field_name, $max_cp);
        //        $spec_min = MinLength::is($field_name, $min_cp);
        //
        //        // Check conf for cp mandatory
        //        $spec_not_null = null;
        //        if ($conf) {
        //            return new AndX($spec_max, $spec_min, NotNull::is($field_name));
        //        }
        //
        //        return new OrX(IsNull::is($field_name), new AndX($spec_max, $spec_min));
        return new OrX(
            new AndX(
                MaxLength::is($field_name, 5),
                MinLength::is($field_name, 5)
            ),
            IsNull::is($field_name)
        );
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface|null
     */
    public function getPaysSpec(string $field_name): ?SpecificationInterface
    {
        if (CAppUI::gconf(self::CONF_ADRESSE)) {
            return new AndX(
                NotNull::is($field_name),
                MaxLength::is($field_name, 80)
            );
        }

        return null;
    }

    /**
     * @param string $spec_name
     * @param bool   $mandatory
     *
     * @return SpecificationInterface
     */
    private function getNaissanceSpec(string $spec_name, bool $mandatory = false): SpecificationInterface
    {
        if ($mandatory) {
            return new AndX(
                NotNull::is($spec_name),
                LessThanOrEqual::is($spec_name, new DateTime()),
                GreaterThanOrEqual::is($spec_name, new DateTime('1850-01-01')),
                InstanceOfX::is($spec_name, DateTime::class)
            );
        }

        return new OrX(
            new AndX(
                LessThanOrEqual::is($spec_name, new DateTime()),
                GreaterThanOrEqual::is($spec_name, new DateTime('1850-01-01')),
                InstanceOfX::is($spec_name, DateTime::class)
            ),
            IsNull::is($spec_name)
        );
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface|null
     */
    public function getMatriculeSpec(string $field_name): ?SpecificationInterface
    {
        return new OrX(
            SpecMatch::is($field_name, '/^\d{13,15}$/'),
            IsNull::is($field_name)
        );
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface|null
     */
    public function getCiviliteSpec(string $field_name): ?SpecificationInterface
    {
        return new OrX(
            Enum::is($field_name, ['m', 'mme', 'mlle', 'enf', 'dr', 'pr', 'me', 'vve']),
            IsNull::is($field_name)
        );
    }

    /**
     * @param string $field_name
     *
     * @return SpecificationInterface|null
     */
    public function getSexeSpec(string $field_name): ?SpecificationInterface
    {
        return new OrX(
            Enum::is($field_name, ['m', 'f', 'u']),
            IsNull::is($field_name)
        );
    }
}
