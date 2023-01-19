<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use Ox\Core\Specification\AndX;
use Ox\Core\Specification\Enum;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\SpecificationInterface;

/**
 * External operation spec builder
 */
class OperationSpecBuilder
{
    use SpecBuilderTrait;

    private const FIELD_ID             = 'external_id';
    private const FIELD_SEJOUR_ID      = 'sejour_id';
    private const FIELD_CHIR_ID        = 'chir_id';
    private const FIELD_COTE           = 'cote';
    private const FIELD_DATE_TIME      = 'date_time';
    private const FIELD_LIBELLE        = 'libelle';
    private const FIELD_EXAMEN         = 'examen';

    public function build(): ?SpecificationInterface
    {
        $spec_to_add = [];

        if ($spec = $this->buildSpec(self::FIELD_SEJOUR_ID)) {
            $spec_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_CHIR_ID)) {
            $spec_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_COTE)) {
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
            case self::FIELD_CHIR_ID:
                return $this->getNotNullSpec($spec_name);

            case self::FIELD_COTE:
                return $this->getCoteSpec();

            default:
                return null;
        }
    }

    /**
     * @return SpecificationInterface
     */
    private function getCoteSpec(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_COTE),
            Enum::is(
                self::FIELD_COTE,
                ['droit', 'gauche', 'haut', 'bas', 'bilatéral', 'total', 'inconnu', 'non_applicable']
            )
        );
    }
}
