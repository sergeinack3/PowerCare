<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use Ox\Core\CAppUI;
use Ox\Core\Specification\AndX;
use Ox\Core\Specification\Enum;
use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\SpecMatch;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationInterface;

/**
 * External medecin spec builder
 */
class MedecinSpecBuilder
{
    use SpecBuilderTrait;

    private const FIELD_ID          = 'external_id';
    private const FIELD_NOM         = 'nom';
    private const FIELD_PRENOM      = 'prenom';
    private const FIELD_SEXE        = 'sexe';
    private const FIELD_TITRE       = 'titre';
    private const FIELD_EMAIL       = 'email';
    private const FIELD_DISCIPLINES = 'disciplines'; // pas de specs particulières
    private const FIELD_TEL         = 'tel';
    private const FIELD_TEL_AUTRE   = 'tel_autre';
    private const FIELD_ADRESSE     = 'adresse';
    private const FIELD_CP          = 'cp';
    private const FIELD_VILLE       = 'ville';
    private const FIELD_RPPS        = 'rpps';
    private const FIELD_ADELI       = 'adeli';

    /**
     * @return SpecificationInterface|null
     */
    public function build(): ?SpecificationInterface
    {
        $specs_to_add = [];

        $specs_to_add[] = $this->buildSpec(self::FIELD_ID);
        $specs_to_add[] = $this->buildSpec(self::FIELD_NOM);

        if ($spec = $this->buildSpec(self::FIELD_PRENOM)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_SEXE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_TITRE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_EMAIL)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_TEL)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_TEL_AUTRE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_ADRESSE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_CP)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_VILLE)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_RPPS)) {
            $specs_to_add[] = $spec;
        }

        if ($spec = $this->buildSpec(self::FIELD_ADELI)) {
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

            case self::FIELD_NOM:
                return $this->getNomSpec(self::FIELD_NOM);

            case self::FIELD_PRENOM:
                return $this->getPrenomSpec(self::FIELD_PRENOM);

            case self::FIELD_SEXE:
                return $this->getSexeSpec();

            case self::FIELD_TITRE:
                return $this->getTitreSpec();

            case self::FIELD_EMAIL:
                return $this->getEmailSpec($spec_name);

            case self::FIELD_TEL:
            case self::FIELD_TEL_AUTRE:
                return $this->getTelSpec($spec_name);

            case self::FIELD_ADRESSE:
                return $this->getAdresseSpec();

            case self::FIELD_CP:
                return $this->getCpSpec($spec_name);

            case self::FIELD_VILLE:
                return $this->getVilleSpec();

            case self::FIELD_RPPS:
                return $this->getRppsSpec();

            case self::FIELD_ADELI:
                return $this->getAdeliSpec();

            default:
                return null;
        }
    }

    /**
     * Build a notNull and notVoid spec
     *
     * @param string $field_name
     *
     * @return NotNull
     */
    private function getNotNullSpec(string $field_name): NotNull
    {
        return NotNull::is($field_name);
    }

    private function getNomSpec(string $field_name): SpecificationInterface
    {
        return new AndX(
            NotNull::is($field_name),
            MaxLength::is($field_name, 255)
        );
    }

    private function getPrenomSpec(string $field_name): ?SpecificationInterface
    {
        return MaxLength::is($field_name, 255);
    }

    /**
     * @return SpecificationInterface|null
     */
    private function getAdresseSpec(): ?SpecificationInterface
    {
        return null;
    }

    /**
     * @return SpecificationInterface|null
     */
    private function getVilleSpec(): ?SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_VILLE),
            MaxLength::is(self::FIELD_VILLE, 255)
        );
    }

    private function getSexeSpec(): MaxLength
    {
        return MaxLength::is(self::FIELD_SEXE, 2);
    }

    private function getTitreSpec(): OrX
    {
        return new OrX(
            Enum::is(self::FIELD_TITRE, ['m', 'mme', 'dr', 'pr']),
            IsNull::is(self::FIELD_TITRE)
        );
    }

    private function getRppsSpec(): Orx
    {
        return new OrX(
            SpecMatch::is(self::FIELD_RPPS, '/^\d{11}$/'),
            IsNull::is(self::FIELD_RPPS)
        );
    }

    private function getAdeliSpec(): OrX
    {
        return new Orx(
            SpecMatch::is(self::FIELD_ADELI, '/^\d{9}$/'),
            IsNull::is(self::FIELD_ADELI)
        );
    }
}
