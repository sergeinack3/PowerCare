<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Cps;

use Ox\Core\CAppUI;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\UserManagement\User;

/**
 * Represents a situation of a CPS
 *
 * @package Ox\Mediboard\Jfse\Domain\CPS
 */
final class Situation extends AbstractEntity
{
    /** @var int */
    protected $practitioner_id;

    /** @var int */
    protected $situation_id;

    /** @var int */
    protected $structure_identifier_type;

    /** @var string */
    protected $structure_identifier;

    /** @var string */
    protected $structure_name;

    /** @var string */
    protected $invoicing_number;

    /** @var string */
    protected $invoicing_number_key;

    /** @var string */
    protected $substitute_number;

    /** @var int */
    protected $convention_code;

    /** @var string */
    protected $convention_label;

    /** @var string */
    protected $speciality_code;

    /** @var string */
    protected $speciality_label;

    /** @var string */
    protected $speciality_group;

    /** @var string */
    protected $price_zone_code;

    /** @var string */
    protected $price_zone_label;

    /** @var string */
    protected $distance_allowance_code;

    /** @var string */
    protected $distance_allowance_label;

    /** @var array */
    protected $approval_codes;

    /** @var array */
    protected $approval_labels;

    /** @var bool */
    protected $fse_signing_authorisation;

    /** @var bool */
    protected $lot_signing_authorisation;

    /** @var int */
    protected $practice_mode;

    /** @var int */
    protected $practice_status;

    /** @var string */
    protected $activity_sector;

    /** @var User */
    protected $user;

    /**
     * @return int
     */
    public function getPractitionerId(): ?int
    {
        return $this->practitioner_id;
    }

    /**
     * @return int
     */
    public function getSituationId(): ?int
    {
        return $this->situation_id;
    }

    /**
     * @return int
     */
    public function getStructureIdentifierType(): ?int
    {
        return $this->structure_identifier_type;
    }

    /**
     * @return string
     */
    public function getStructureIdentifier(): ?string
    {
        return $this->structure_identifier;
    }

    /**
     * @return string
     */
    public function getStructureName(): ?string
    {
        return $this->structure_name;
    }

    /**
     * @return string
     */
    public function getInvoicingNumber(): ?string
    {
        return $this->invoicing_number;
    }

    /**
     * @return string
     */
    public function getInvoicingNumberKey(): ?string
    {
        return $this->invoicing_number_key;
    }

    /**
     * @return string
     */
    public function getSubstituteNumber(): ?string
    {
        return $this->substitute_number;
    }

    /**
     * @return int
     */
    public function getConventionCode(): ?int
    {
        return $this->convention_code;
    }

    /**
     * @return string
     */
    public function getConventionLabel(): ?string
    {
        if (!$this->convention_label && $this->convention_code) {
            $this->convention_label = CAppUI::tr("CCpsSituation.convention_label.{$this->convention_code}");
        }

        return $this->convention_label;
    }

    /**
     * @return string
     */
    public function getSpecialityCode(): ?string
    {
        return $this->speciality_code;
    }

    /**
     * @return string
     */
    public function getSpecialityLabel(): ?string
    {
        return $this->speciality_label;
    }

    /**
     * @return string
     */
    public function getSpecialityGroup(): ?string
    {
        return $this->speciality_group;
    }

    /**
     * @return string
     */
    public function getPriceZoneCode(): ?string
    {
        return $this->price_zone_code;
    }

    /**
     * @return string
     */
    public function getPriceZoneLabel(): ?string
    {
        return $this->price_zone_label;
    }

    /**
     * @return string
     */
    public function getDistanceAllowanceCode(): ?string
    {
        return $this->distance_allowance_code;
    }

    /**
     * @return string
     */
    public function getDistanceAllowanceLabel(): ?string
    {
        return $this->distance_allowance_label;
    }

    /**
     * @return array
     */
    public function getApprovalCodes(): array
    {
        return $this->approval_codes;
    }

    /**
     * @return array
     */
    public function getApprovalLabels(): array
    {
        return $this->approval_labels;
    }

    /**
     * @return bool
     */
    public function getFseSigningAuthorisation(): ?bool
    {
        return $this->fse_signing_authorisation;
    }

    /**
     * @return bool
     */
    public function getLotSigningAuthorisation(): ?bool
    {
        return $this->lot_signing_authorisation;
    }

    /**
     * @return int
     */
    public function getPracticeMode(): ?int
    {
        return $this->practice_mode;
    }

    /**
     * @return int
     */
    public function getPracticeStatus(): ?int
    {
        return $this->practice_status;
    }

    /**
     * @return string
     */
    public function getActivitySector(): ?string
    {
        return $this->activity_sector;
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
