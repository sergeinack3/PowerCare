<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\MedicalAct;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class Pricing extends AbstractEntity
{
    /** @var float */
    protected $exceeding_amount;

    /** @var float */
    protected $total_amount;

    /** @var int */
    protected $additional_charge;

    /** @var bool */
    protected $exceptional_reimbursement;

    /** @var float */
    protected $unit_price;

    /** @var float */
    protected $reimbursement_base;

    /** @var float */
    protected $referential_price;

    /** @var int */
    protected $rate;

    /** @var float */
    protected $invoice_total;

    /** @var float */
    protected $total_amo;

    /** @var float */
    protected $total_insured;

    /** @var float */
    protected $total_amc;

    /** @var float */
    protected $owe_amo;

    /** @var float */
    protected $owe_amc;

    /**
     * @return float
     */
    public function getExceedingAmount(): ?float
    {
        return $this->exceeding_amount;
    }

    /**
     * @return float
     */
    public function getTotalAmount(): ?float
    {
        return $this->total_amount;
    }

    /**
     * @return int
     */
    public function getAdditionalCharge(): ?int
    {
        return $this->additional_charge;
    }

    /**
     * @return bool
     */
    public function getExceptionalReimbursement(): ?bool
    {
        return $this->exceptional_reimbursement;
    }

    /**
     * @return float
     */
    public function getUnitPrice(): ?float
    {
        return $this->unit_price;
    }

    /**
     * @return float
     */
    public function getReimbursementBase(): ?float
    {
        return $this->reimbursement_base;
    }

    /**
     * @return float
     */
    public function getReferentialPrice(): ?float
    {
        return $this->referential_price;
    }

    /**
     * @return int
     */
    public function getRate(): ?int
    {
        return $this->rate;
    }

    /**
     * @return float
     */
    public function getInvoiceTotal(): ?float
    {
        return $this->invoice_total;
    }

    /**
     * @return float
     */
    public function getTotalAmo(): ?float
    {
        return $this->total_amo;
    }

    /**
     * @return float
     */
    public function getTotalInsured(): ?float
    {
        return $this->total_insured;
    }

    /**
     * @return float
     */
    public function getTotalAmc(): ?float
    {
        return $this->total_amc;
    }

    /**
     * @return float
     */
    public function getOweAmo(): ?float
    {
        return $this->owe_amo;
    }

    /**
     * @return float
     */
    public function getOweAmc(): ?float
    {
        return $this->owe_amc;
    }
}
