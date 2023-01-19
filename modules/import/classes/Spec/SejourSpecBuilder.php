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
use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationInterface;

/**
 * External sejour spec builder
 */
class SejourSpecBuilder
{
    use SpecBuilderTrait;

    private const FIELD_ID              = 'external_id';
    private const FIELD_TYPE            = 'type';
    private const FIELD_ENTREE          = 'entree';
    private const FIELD_ENTREE_PREVUE   = 'entree_prevue';
    private const FIELD_ENTREE_REELLE   = 'entree_reelle';
    private const FIELD_SORTIE          = 'sortie';
    private const FIELD_SORTIE_PREVUE   = 'sortie_prevue';
    private const FIELD_SORTIE_REELLE   = 'sortie_reelle';
    private const FIELD_LIBELLE         = 'libelle';
    private const FIELD_PATIENT_ID      = 'patient_id';
    private const FIELD_PRATICIEN_ID    = 'praticien_id';
    private const FIELD_PRESTATION      = 'prestation';
    private const FIELD_NDA             = 'nda';
    private const FIELD_MODE_TRAITEMENT = 'mode_traitement';
    private const FIELD_MODE_ENTREE     = 'mode_entree';
    private const FIELD_MODE_SORTIE     = 'mode_sortie';

    public function build(): ?SpecificationInterface
    {
        $spec_to_add = [
            $this->buildSpec(self::FIELD_ID),
            $this->buildSpec(self::FIELD_PATIENT_ID),
            $this->buildSpec(self::FIELD_PRATICIEN_ID),
            $this->buildSpec(self::FIELD_ENTREE),
            $this->buildSpec(self::FIELD_SORTIE),
        ];

        if ($spec = $this->buildSpec(self::FIELD_TYPE)) {
            $spec_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_LIBELLE)) {
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
            case self::FIELD_PATIENT_ID:
            case self::FIELD_PRATICIEN_ID:
                return $this->getNotNullSpec($spec_name);

            case self::FIELD_ENTREE:
                return $this->getEntreeSpec();

            case self::FIELD_SORTIE:
                return $this->getSortieSpec();

            case self::FIELD_LIBELLE:
                return $this->getLibelleSpec();

            case self::FIELD_TYPE:
                return $this->getTypeSpec();

            default:
                return null;
        }
    }

    private function getEntreeSpec(): SpecificationInterface
    {
        $specs   = [];
        $spec    = new OrX(
            IsNull::is(self::FIELD_ENTREE_REELLE),
            new AndX(
                NotNull::is(self::FIELD_ENTREE_REELLE),
                InstanceOfX::is(self::FIELD_ENTREE_REELLE, DateTime::class)
            )
        );
        $specs[] = $spec;
        $spec    = new AndX(
            NotNull::is(self::FIELD_ENTREE_PREVUE),
            InstanceOfX::is(self::FIELD_ENTREE_PREVUE, DateTime::class)
        );
        $specs[] = $spec;

        return new AndX(...$specs);
    }

    private function getSortieSpec(): SpecificationInterface
    {
        $specs   = [];
        $spec    = new OrX(
            new AndX(
                IsNull::is(self::FIELD_SORTIE_REELLE),
                IsNull::is(self::FIELD_ENTREE_REELLE)
            ),
            new AndX(
                NotNull::is(self::FIELD_SORTIE_REELLE),
                InstanceOfX::is(self::FIELD_SORTIE_REELLE, DateTime::class),
            )
        );
        $specs[] = $spec;
        $spec    = new AndX(
            NotNull::is(self::FIELD_SORTIE_PREVUE),
            InstanceOfX::is(self::FIELD_SORTIE_PREVUE, DateTime::class),
        );
        $specs[] = $spec;

        return new AndX(...$specs);
    }

    private function getLibelleSpec(): OrX
    {
        return new OrX(
            MaxLength::is(self::FIELD_LIBELLE, 255),
            IsNull::is(self::FIELD_LIBELLE)
        );
    }

    private function getTypeSpec(): SpecificationInterface
    {
        return new AndX(
            NotNull::is(self::FIELD_TYPE),
            Enum::is(self::FIELD_TYPE, ['comp', 'ambu', 'exte', 'seances', 'ssr', 'psy', 'urg', 'consult'])
        );
    }
}
