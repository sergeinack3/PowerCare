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
 * External sejour representation
 */
class Sejour extends AbstractEntity
{
    public const EXTERNAL_CLASS = 'SEJR';

    /** @var string */
    protected $type;

    /** @var DateTime */
    protected $entree_prevue;

    /** @var DateTime */
    protected $entree_reelle;

    /** @var DateTime */
    protected $sortie_prevue;

    /** @var DateTime */
    protected $sortie_reelle;

    /** @var string */
    protected $libelle;

    /** @var string */
    protected $patient_id;

    /** @var string */
    protected $praticien_id;

    /** @var string */
    protected $prestation;

    /** @var string */
    protected $nda;

    /** @var string */
    protected $mode_traitement;

    /** @var string */
    protected $mode_entree;

    /** @var string */
    protected $mode_sortie;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateSejour($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformSejour($this, $reference_stash, $campaign);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultRefEntities(): array
    {
        return [
            ExternalReference::getMandatoryFor(ExternalReference::UTILISATEUR, $this->praticien_id),
            ExternalReference::getMandatoryFor(ExternalReference::PATIENT, $this->patient_id),
            // TODO Handle Group
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return DateTime
     */
    public function getEntreePrevue(): DateTime
    {
        return $this->entree_prevue;
    }

    /**
     * @return null|DateTime
     */
    public function getEntreeReelle(): ?DateTime
    {
        return $this->entree_reelle;
    }

    /**
     * @return DateTime
     */
    public function getSortiePrevue(): DateTime
    {
        return $this->sortie_prevue;
    }

    /**
     * @return null|DateTime
     */
    public function getSortieReelle(): ?DateTime
    {
        return $this->sortie_reelle;
    }

    /**
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

    /**
     * @return string
     */
    public function getPatientId(): string
    {
        return $this->patient_id;
    }

    /**
     * @return string
     */
    public function getPraticienId(): string
    {
        return $this->praticien_id;
    }

    /**
     * @return null|string
     */
    public function getNda(): ?string
    {
        return $this->nda;
    }

    /**
     * @return null|string
     */
    public function getModeTraitement(): ?string
    {
        return $this->mode_traitement;
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
    public function getPrestation(): ?string
    {
        return $this->prestation;
    }
}
