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
 * External patient representation
 */
class Antecedent extends AbstractEntity
{
    public const EXTERNAL_CLASS = 'ATCD';

    /** @var string */
    protected $owner_id;

    /** @var string */
    protected $patient_id;

    /** @var string */
    protected $text;

    /** @var string */
    protected $comment;

    /** @var DateTime */
    protected $date;

    /** @var string */
    protected $type;

    /** @var string */
    protected $appareil;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateAntecedent($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformAntecedent($this, $reference_stash, $campaign);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultRefEntities(): array
    {
        return [
            ExternalReference::getNotMandatoryFor(ExternalReference::UTILISATEUR, $this->owner_id),
            ExternalReference::getMandatoryFor(ExternalReference::PATIENT, $this->patient_id),
        ];
    }

    /**
     * TODO Use this for DFSImport
     *
     * @return array
     */
    public function getCollectionsObjects(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getExternalClass()
    {
        return static::EXTERNAL_CLASS;
    }

    /**
     * @return mixed
     */
    public function getExternalId()
    {
        return $this->external_id;
    }

    /**
     * @return callable
     */
    public function getCustomRefEntitiesCallable(): callable
    {
        return $this->custom_ref_entities_callable;
    }

    /**
     * @return string
     */
    public function getOwnerId(): ?string
    {
        return $this->owner_id;
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
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return DateTime
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getAppareil(): ?string
    {
        return $this->appareil;
    }
}
