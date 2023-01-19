<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\MedicalAct;

use DateTime;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class LppBenefit extends AbstractEntity
{
    /** @var string */
    protected $code;

    /** @var LppTypeEnum */
    protected $type;

    /** @var string */
    protected $label;

    /** @var int */
    protected $quantity;

    /** @var string */
    protected $siret_number;

    /** @var float */
    protected $unit_price_ref;

    /** @var float */
    protected $unit_price_ttc;

    /** @var float */
    protected $total_price_ref;

    /** @var float */
    protected $total_price_ttc;

    /** @var DateTime|null */
    protected $end_date;

    /** @var DateTime|null */
    protected $begin_date;

    /** @var float */
    protected $sell_price_limit;

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return LppTypeEnum
     */
    public function getType(): ?LppTypeEnum
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return int
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @return string
     */
    public function getSiretNumber(): ?string
    {
        return $this->siret_number;
    }

    /**
     * @return float
     */
    public function getUnitPriceRef(): ?float
    {
        return $this->unit_price_ref;
    }

    /**
     * @return float
     */
    public function getUnitPriceTtc(): ?float
    {
        return $this->unit_price_ttc;
    }

    /**
     * @return float
     */
    public function getTotalPriceRef(): ?float
    {
        return $this->total_price_ref;
    }

    /**
     * @return float
     */
    public function getTotalPriceTtc(): ?float
    {
        return $this->total_price_ttc;
    }

    /**
     * @return DateTime|null
     */
    public function getEndDate(): ?DateTime
    {
        return $this->end_date;
    }

    /**
     * @return DateTime|null
     */
    public function getBeginDate(): ?DateTime
    {
        return $this->begin_date;
    }

    /**
     * @return float
     */
    public function getSellPriceLimit(): ?float
    {
        return $this->sell_price_limit;
    }
}
