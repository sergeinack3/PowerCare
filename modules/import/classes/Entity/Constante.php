<?php

/**
 * @package Mediboard\Import
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
 * Representation of a Constante for importation
 */
class Constante extends AbstractEntity
{
    public const EXTERNAL_CLASS = 'CONS';

    /** @var string */
    protected $user_id;

    /** @var string */
    protected $patient_id;

    /** @var DateTime */
    protected $datetime;

    /** @var string */
    protected $poids;

    /** @var string */
    protected $taille;

    /** @var int|null */
    protected $pouls;

    /** @var float|null */
    protected $temperature;

    /** @var float|null */
    protected $ta_droit_systole;

    /** @var float|null */
    protected $ta_droit_diastole;

    /** @var float|null */
    protected $ta_gauche_systole;

    /** @var float|null */
    protected $ta_gauche_diastole;

    /** @var int|null */
    protected $pointure;

    public function getDefaultRefEntities(): array
    {
        return [
            ExternalReference::getNotMandatoryFor(ExternalReference::UTILISATEUR, $this->user_id),
            ExternalReference::getMandatoryFor(ExternalReference::PATIENT, $this->patient_id),
        ];
    }

    public function getExternalClass()
    {
        return self::EXTERNAL_CLASS;
    }

    public function transform(
        TransformerVisitorInterface $transformer,
        ?ExternalReferenceStash $reference_stash = null,
        ?CImportCampaign $campaign = null
    ): ImportableInterface {
        return $transformer->transformConstante($this, $reference_stash, $campaign);
    }

    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation
    {
        return $validator->validateConstante($this);
    }

    public function getUserId(): ?string
    {
        return $this->user_id;
    }

    public function getPatientId(): string
    {
        return $this->patient_id;
    }

    public function getDatetime(): ?DateTime
    {
        return $this->datetime;
    }

    public function getPoids(): ?string
    {
        return $this->poids;
    }

    public function getTaille(): ?string
    {
        return $this->taille;
    }

    /**
     * @return int|null
     */
    public function getPouls(): ?int
    {
        return $this->pouls;
    }

    /**
     * @return float|null
     */
    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    /**
     * @return float|null
     */
    public function getTaDroitSystole(): ?float
    {
        return $this->ta_droit_systole;
    }

    /**
     * @return float|null
     */
    public function getTaDroitDiastole(): ?float
    {
        return $this->ta_droit_diastole;
    }

    /**
     * @return float|null
     */
    public function getTaGaucheSystole(): ?float
    {
        return $this->ta_gauche_systole;
    }

    /**
     * @return float|null
     */
    public function getTaGaucheDiastole(): ?float
    {
        return $this->ta_gauche_diastole;
    }

    /**
     * @return int|null
     */
    public function getPointure(): ?int
    {
        return $this->pointure;
    }
}
