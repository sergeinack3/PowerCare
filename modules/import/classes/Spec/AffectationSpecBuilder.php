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
use Ox\Core\Specification\InstanceOfX;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\SpecificationInterface;

/**
 * External affectation spec builder
 */
class AffectationSpecBuilder
{
    use SpecBuilderTrait;

    private const FIELD_ID          = 'external_id';
    private const FIELD_SEJOUR_ID   = 'sejour_id';
    private const FIELD_NOM_SERVICE = 'nom_service';
    private const FIELD_NOM_LIT     = 'nom_lit';
    private const FIELD_ENTREE      = 'entree';
    private const FIELD_SORTIE      = 'sortie';
    private const FIELD_REMARQUES   = 'remarques';
    private const FIELD_EFFECTUE    = 'effectue';
    private const FIELD_MODE_ENTREE = 'mode_entree';
    private const FIELD_MODE_SORTIE = 'mode_sortie';
    private const FIELD_CODE_UF     = 'code_unite_fonctionnelle';

    public function build(): ?SpecificationInterface
    {
        $spec_to_add = [];
        if ($spec = $this->buildSpec(self::FIELD_SEJOUR_ID)) {
            $spec_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_NOM_SERVICE)) {
            $spec_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_NOM_LIT)) {
            $spec_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_ENTREE)) {
            $spec_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_SORTIE)) {
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
    private function buildSpec(string $spec_name): ?SpecificationInterface
    {
        switch ($spec_name) {
            case self::FIELD_ID:
            case self::FIELD_SEJOUR_ID:
                return $this->getNotNullSpec($spec_name);

            case self::FIELD_NOM_LIT:
                return $this->getStringSpec($spec_name);

            case self::FIELD_NOM_SERVICE:
                return $this->getStringNotNullSpec($spec_name);

            case self::FIELD_ENTREE:
            case self::FIELD_SORTIE:
                return $this->getEntreeSortieSpec($spec_name);

            case self::FIELD_MODE_SORTIE:
                return $this->getModeSortieSpec($spec_name);

            default:
                return null;
        }
    }

    private function getEntreeSortieSpec(string $field_name): SpecificationInterface
    {
        return new AndX(
            NotNull::is($field_name),
            InstanceOfX::is($field_name, DateTime::class)
        );
    }


    private function getModeSortieSpec(string $field_name): SpecificationInterface
    {
        return new AndX(
            Enum::is($field_name, ['0', '4', '5', '6', '7', '8', '9']),
            MaxLength::is($field_name, 255)
        );
    }
}
