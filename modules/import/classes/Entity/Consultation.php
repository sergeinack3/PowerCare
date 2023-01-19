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
 * External consultation repsresentation
 */
class Consultation extends AbstractEntity
{
    public const EXTERNAL_CLASS = 'CSLT';

    /** @var DateTime */
    protected $heure;

    /** @var int */
    protected $duree;

    /** @var string */
    protected $motif;

    /** @var string */
    protected $rques;

    /** @var string */
    protected $examen;

    /** @var string */
    protected $traitement;

    /** @var string */
    protected $histoire_maladie;

    /** @var string */
    protected $conclusion;

    /** @var string */
    protected $resultats;

    protected $chrono = '64';

    /** @var mixed */
    protected $plageconsult_id;

    /** @var mixed */
    protected $patient_id;

    protected $default_refs = true;

    /**
     * @inheritDoc
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateConsultation($this);
    }

    /**
     * @inheritDoc
     */
    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformConsultation($this, $reference_stash, $campaign);
    }

    /**
     * Get the refs objects to to import
     *
     * @return array
     */
    public function getDefaultRefEntities(): array
    {
        if ($this->default_refs) {
            $refs = [
                ExternalReference::getMandatoryFor(ExternalReference::PATIENT, $this->patient_id),
                ExternalReference::getMandatoryFor(ExternalReference::PLAGE_CONSULTATION, $this->plageconsult_id),
            ];
        } else {
            $refs = $this->getAlternateRefEntities();
        }

        return $refs;
    }

    public function getAlternateRefEntities(): array
    {
        return [
            ExternalReference::getMandatoryFor(ExternalReference::PATIENT, $this->patient_id),
            ExternalReference::getMandatoryFor(ExternalReference::PLAGE_CONSULTATION_AUTRE, $this->plageconsult_id),
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
     * @return DateTime
     */
    public function getHeure(): ?DateTime
    {
        return $this->heure;
    }

    /**
     * @return DateTime
     */
    public function getDuree(): ?string
    {
        return $this->duree;
    }

    /**
     * @return string
     */
    public function getMotif(): ?string
    {
        return $this->motif;
    }

    /**
     * @return string
     */
    public function getRques(): ?string
    {
        return $this->rques;
    }

    /**
     * @return string
     */
    public function getExamen(): ?string
    {
        return $this->examen;
    }

    /**
     * @return string
     */
    public function getTraitement(): ?string
    {
        return $this->traitement;
    }

    /**
     * @return string
     */
    public function getHistoireMaladie(): ?string
    {
        return $this->histoire_maladie;
    }

    /**
     * @return string
     */
    public function getConclusion(): ?string
    {
        return $this->conclusion;
    }

    /**
     * @return string
     */
    public function getResultats(): ?string
    {
        return $this->resultats;
    }

    /**
     * @return mixed
     */
    public function getPlageconsultId()
    {
        return $this->plageconsult_id;
    }

    /**
     * @return mixed
     */
    public function getPatientId()
    {
        return $this->patient_id;
    }

    public function getChrono()
    {
        return $this->chrono;
    }

    public function getCollections(): array
    {
        return [
            'fichier'   => 'consultation_id',
            'document' => 'consultation_id',
        ];
    }

    public function setDefaultRefs(bool $default_ref): void
    {
        $this->default_refs = $default_ref;
    }

    public function getDefaultRefs(): bool
    {
        return $this->default_refs;
    }
}
