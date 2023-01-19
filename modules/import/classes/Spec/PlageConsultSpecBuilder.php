<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use DateTime;
use Exception;
use Ox\Core\Specification\AndX;
use Ox\Core\Specification\GreaterThan;
use Ox\Core\Specification\GreaterThanOrEqual;
use Ox\Core\Specification\InstanceOfX;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\SpecificationInterface;

/**
 * External plageconsult spec builder
 */
class PlageConsultSpecBuilder
{
    private const FIELD_ID      = 'external_id';
    private const FIELD_CHIR    = 'chir_id';
    private const FIELD_DATE    = 'date';
    private const FIELD_FREQ    = 'freq';
    private const FIELD_DEBUT   = 'debut';
    private const FIELD_FIN     = 'fin';
    private const FIELD_LIBELLE = 'libelle';

    private const MIN_FREQ = '00:05:00';
    private const DEBUT_PLAGE_CONSULT = '12:00:00';

    /**
     * @return SpecificationInterface|null
     * @throws Exception
     */
    public function build(): ?SpecificationInterface
    {
        $specs_to_add = [
            $this->buildSpec(self::FIELD_ID),
            $this->buildSpec(self::FIELD_CHIR),
            $this->buildSpec(self::FIELD_DATE),
            $this->buildSpec(self::FIELD_FREQ),
            $this->buildSpec(self::FIELD_DEBUT),
            $this->buildSpec(self::FIELD_FIN),
            $this->buildSpec(self::FIELD_LIBELLE),
        ];

        // Todo: Handle null (currently not allowed in Composite specification)
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
            case self::FIELD_CHIR:
                return $this->getNotNullSpec($spec_name);

            case self::FIELD_DATE:
                return $this->getDateSpec();

            case self::FIELD_FREQ:
                return $this->getFreqSpec();

            case self::FIELD_DEBUT:
                return $this->getDebutSpec();

            case self::FIELD_FIN:
                return $this->getFinSpec();

            case self::FIELD_LIBELLE:
                return $this->getLibelleSpec();

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

    /**
     * @return SpecificationInterface
     */
    private function getDateSpec(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_DATE),
            InstanceOfX::is(self::FIELD_DATE, DateTime::class)
        );
    }

    /**
     * @return SpecificationInterface
     * @throws Exception
     */
    private function getFreqSpec(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_FREQ),
            InstanceOfX::is(self::FIELD_FREQ, DateTime::class),
            GreaterThanOrEqual::is(self::FIELD_FREQ, new DateTime(self::MIN_FREQ))
        );
    }

    /**
     * @return SpecificationInterface
     */
    private function getDebutSpec(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_DEBUT),
            InstanceOfX::is(self::FIELD_DEBUT, DateTime::class)
        );
    }

    /**
     * @return SpecificationInterface
     */
    private function getFinSpec(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_FIN),
            InstanceOfX::is(self::FIELD_FIN, DateTime::class),
            GreaterThan::is(self::FIELD_FIN, new DateTime(self::DEBUT_PLAGE_CONSULT))
        );
    }

    private function getLibelleSpec(): SpecificationInterface
    {
        return MaxLength::is(self::FIELD_LIBELLE, 255);
    }
}
