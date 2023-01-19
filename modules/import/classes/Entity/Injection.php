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
 * External CInjection representation
 */
class Injection extends AbstractEntity
{
    public const EXTERNAL_CLASS = 'INJ';

    protected string    $patient_id;
    protected ?string   $practitioner_name;
    protected DateTime  $injection_date;
    protected ?string   $batch;
    protected ?string   $speciality;
    protected ?string   $remarques;
    protected ?string   $cip_product;
    protected ?DateTime $expiration_date;
    protected ?int      $recall_age;
    protected ?string   $_type_vaccin;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateInjection($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformInjection($this, $reference_stash, $campaign);
    }

    /**
     * Get the refs objects to import
     *
     * @return array
     */
    public function getDefaultRefEntities(): array
    {
        return [
            ExternalReference::getMandatoryFor(ExternalReference::PATIENT, $this->patient_id),
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
    public function getPatientId(): string
    {
        return $this->patient_id;
    }

    /**
     * @return string
     */
    public function getPractitionerName(): ?string
    {
        return $this->practitioner_name;
    }

    /**
     * @return DateTime
     */
    public function getInjectionDate(): DateTime
    {
        return $this->injection_date;
    }

    /**
     * @return string
     */
    public function getBatch(): ?string
    {
        return $this->batch;
    }

    /**
     * @return string
     */
    public function getSpeciality(): ?string
    {
        return $this->speciality;
    }

    /**
     * @return string
     */
    public function getRemarques(): ?string
    {
        return $this->remarques;
    }

    /**
     * @return string
     */
    public function getCipProduct(): ?string
    {
        return $this->cip_product;
    }

    /**
     * @return DateTime
     */
    public function getExpirationDate(): ?DateTime
    {
        return $this->expiration_date;
    }

    /**
     * @return int
     */
    public function getRecallAge(): ?int
    {
        return $this->recall_age;
    }

    /**
     * @return string|null
     */
    public function getTypeVaccin(): ?string
    {
        return $this->_type_vaccin;
    }
}
