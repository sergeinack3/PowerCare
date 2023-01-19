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
class Traitement extends AbstractEntity
{
    private const EXTERNAL_CLASS = 'TRT';

    /** @var DateTime */
    protected $debut;

    /** @var DateTime */
    protected $fin;

    /** @var string */
    protected $traitement;

    /** @var string */
    protected $patient_id;

    /** @var string */
    protected $owner_id;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateTraitement($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformTraitement($this, $reference_stash, $campaign);
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
    public function getTraitement(): ?string
    {
        return $this->traitement;
    }

    public function getDebut(): ?DateTime
    {
        return $this->debut;
    }

    public function getFin(): ?DateTime
    {
        return $this->fin;
    }
}
