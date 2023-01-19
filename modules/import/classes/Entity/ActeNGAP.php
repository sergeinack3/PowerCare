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

class ActeNGAP extends AbstractEntity
{
    protected const EXTERNAL_CLASS = 'NGAP';

    /** @var string */
    protected $executant_id;

    /** @var string */
    protected $consultation_id;

    /** @var string */
    protected $code_acte;

    /** @var DateTime */
    protected $date_execution;

    /** @var int */
    protected $quantite;

    /** @var float */
    protected $coefficient;

    /** @var float */
    protected $montant_base;

    /** @var float */
    protected $montant_depassement;

    /** @var int */
    protected $numero_dent;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateActeNGAP($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformActeNGAP($this, $reference_stash, $campaign);
    }

    /**
     * @inheritDoc
     */
    public function getExternalClass()
    {
        return static::EXTERNAL_CLASS;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultRefEntities(): array
    {
        return [
            ExternalReference::getMandatoryFor(ExternalReference::UTILISATEUR, $this->executant_id),
            ExternalReference::getMandatoryFor(ExternalReference::CONSULTATION, $this->consultation_id),
        ];
    }

    public function getExecutantId(): string
    {
        return $this->executant_id;
    }

    public function getConsultationId(): string
    {
        return $this->consultation_id;
    }

    public function getCodeActe(): string
    {
        return $this->code_acte;
    }

    public function getDateExecution(): DateTime
    {
        return $this->date_execution;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function getCoefficient(): float
    {
        return $this->coefficient;
    }

    public function getMontantBase(): ?float
    {
        return $this->montant_base;
    }

    public function getMontantDepassement(): ?float
    {
        return $this->montant_depassement;
    }

    public function getNumeroDent(): ?int
    {
        return $this->numero_dent;
    }
}
