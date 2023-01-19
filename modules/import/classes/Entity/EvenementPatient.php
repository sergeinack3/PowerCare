<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use DateTime;
use Ox\Core\CStoredObject;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Transformer\TransformerVisitorInterface;
use Ox\Import\Framework\Validator\ValidatorVisitorInterface;

/**
 * Generic Import - Generic
 * EvenementPatient
 */
class EvenementPatient extends AbstractEntity
{
    public const EXTERNAL_CLASS = 'EVTPA';

    /** @var string|null $patient_id Patient associated with the event */
    protected ?string $patient_id;

    /** @var string|null $practitioner_id Doctor in charge of the event */
    protected ?string $practitioner_id;

    /** @var DateTime|null $datetime Datetime of the event */
    protected ?DateTime $datetime;

    /** @var string|null $label Label of the event */
    protected ?string $label;

    /** @var string|null $type Type of the event */
    protected ?string $type;

    /** @var string|null $description Description of the event */
    protected ?string $description;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateEvenementPatient($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformEvenementPatient($this, $reference_stash, $campaign);
    }

    /**
     * @inheritDoc
     */
    public function getExternalClass(): string
    {
        return static::EXTERNAL_CLASS;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultRefEntities(): array
    {
        return [
            ExternalReference::getMandatoryFor(ExternalReference::PATIENT, $this->patient_id),
            ExternalReference::getMandatoryFor(ExternalReference::UTILISATEUR, $this->practitioner_id),
        ];
    }

    /**
     * @return string|null
     */
    public function getPatientId(): ?string
    {
        return $this->patient_id;
    }

    /**
     * @return string|null
     */
    public function getPractitionerId(): ?string
    {
        return $this->practitioner_id;
    }

    /**
     * @return DateTime|null
     */
    public function getDatetime(): ?DateTime
    {
        return $this->datetime;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
}
