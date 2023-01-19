<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use DateTime;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Transformer\TransformerVisitorInterface;
use Ox\Import\Framework\Validator\ValidatorVisitorInterface;

/**
 * External affectation representation
 */
class Affectation extends AbstractEntity
{
    public const EXTERNAL_CLASS = 'AFCT';

    /** @var string */
    protected $sejour_id;

    /** @var string */
    protected $nom_service;

    /** @var string */
    protected $nom_lit;

    /** @var DateTime */
    protected $entree;

    /** @var DateTime */
    protected $sortie;

    /** @var string */
    protected $remarques;

    /** @var string */
    protected $effectue;

    /** @var string */
    protected $mode_entree;

    /** @var string */
    protected $mode_sortie;

    /** @var string */
    protected $code_uf;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateAffectation($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformAffectation($this, $reference_stash, $campaign);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultRefEntities(): array
    {
        return [
            ExternalReference::getMandatoryFor(ExternalReference::SEJOUR, $this->sejour_id),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getExternalClass()
    {
        return static::EXTERNAL_CLASS;
    }

    /**
     * @return string
     */
    public function getSejourId(): string
    {
        return $this->sejour_id;
    }

    /**
     * @return string
     */
    public function getNomService(): string
    {
        return $this->nom_service;
    }

    /**
     * @return null|string
     */
    public function getNomLit(): ?string
    {
        return $this->nom_lit;
    }

    /**
     * @return DateTime
     */
    public function getEntree(): DateTime
    {
        return $this->entree;
    }

    /**
     * @return DateTime
     */
    public function getSortie(): DateTime
    {
        return $this->sortie;
    }

    /**
     * @return null|string
     */
    public function getRemarques(): ?string
    {
        return $this->remarques;
    }

    /**
     * @return null|string
     */
    public function getEffectue(): ?string
    {
        return $this->effectue;
    }

    /**
     * @return null|string
     */
    public function getModeEntree(): ?string
    {
        return $this->mode_entree;
    }

    /**
     * @return null|string
     */
    public function getModeSortie(): ?string
    {
        return $this->mode_sortie;
    }

    /**
     * @return null|string
     */
    public function getCodeUf(): ?string
    {
        return $this->code_uf;
    }
}
