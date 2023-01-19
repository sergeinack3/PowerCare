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
 * External consultation spec builder
 */
class ConsultationSpecBuilder
{
    private const FIELD_ID      = 'external_id';
    private const FIELD_HEURE   = 'heure';
    private const FIELD_DUREE   = 'duree';
    private const FIELD_MOTIF   = 'motif';
    private const FIELD_CHRONO  = 'chrono';
    private const FIELD_PLAGE   = 'plageconsult_id';
    private const FIELD_PATIENT = 'patient_id';

    /**
     * @return SpecificationInterface|null
     */
    public function build(): ?SpecificationInterface
    {
        $specs_to_add = [
            $this->buildSpec(self::FIELD_ID),
            $this->buildSpec(self::FIELD_HEURE),
            $this->buildSpec(self::FIELD_DUREE),
            $this->buildSpec(self::FIELD_MOTIF),
            $this->buildSpec(self::FIELD_PLAGE),
        ];
        if ($spec = $this->buildSpec(self::FIELD_CHRONO)) {
            $specs_to_add[] = $spec;
        }
        if ($spec = $this->buildSpec(self::FIELD_PATIENT)) {
            $specs_to_add[] = $spec;
        }

        // Todo: Handle null (currently not allowed in Composite specification)
        return new AndX(...$specs_to_add);
    }

    /**
     * Build spec depending on $spec_name
     *
     * @param string $spec_name
     *
     * @return SpecificationInterface|null
     */
    private function buildSpec(string $spec_name): ?SpecificationInterface
    {
        switch ($spec_name) {
            case self::FIELD_ID:
                return $this->getNotNullSpec(self::FIELD_ID);

            case self::FIELD_HEURE:
                return $this->getHeureSpec();

            case self::FIELD_DUREE:
                return $this->getDureeSpec();

            case self::FIELD_MOTIF:
                return $this->getNotNullSpec(self::FIELD_MOTIF);

            case self::FIELD_PLAGE:
                return $this->getNotNullSpec(self::FIELD_PLAGE);

            case self::FIELD_CHRONO:
                return $this->getChronoSpec();

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
    private function getHeureSpec(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_HEURE),
            InstanceOfX::is(self::FIELD_HEURE, DateTime::class)
        );
    }

    /**
     * @return SpecificationInterface
     */
    private function getDureeSpec(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_DUREE),
            GreaterThanOrEqual::is(self::FIELD_DUREE, 1),
            LessThanOrEqual::is(self::FIELD_DUREE, 255)
        );
    }

    private function getChronoSpec(): Orx
    {
        return new OrX(
            Enum::is(self::FIELD_CHRONO, ['8', '16', '32', '48', '64']),
            IsNull::is(self::FIELD_CHRONO)
        );
    }
}
