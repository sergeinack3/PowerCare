<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\UserManagement;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class EstablishmentConfiguration extends AbstractEntity
{
    /** @var int */
    protected $invoice_number;

    /** @var int */
    protected $invoice_set_number;

    /** @var int */
    protected $refund_demand_number;

    /** @var int */
    protected $maximum_invoice_set_number;

    /** @var int */
    protected $maximum_refund_demand_number;

    /** @var int */
    protected $desired_invoice_number;

    /** @var int */
    protected $file_number;

    /** @var bool  */
    protected $invoice_number_range_activation;

    /** @var int */
    protected $invoice_number_range_start;

    /** @var int */
    protected $invoice_number_range_end;

    /**
     * @return int
     */
    public function getInvoiceNumber(): ?int
    {
        return $this->invoice_number;
    }

    /**
     * @return int
     */
    public function getInvoiceSetNumber(): ?int
    {
        return $this->invoice_set_number;
    }

    /**
     * @return int
     */
    public function getRefundDemandNumber(): ?int
    {
        return $this->refund_demand_number;
    }

    /**
     * @return int
     */
    public function getMaximumInvoiceSetNumber(): ?int
    {
        return $this->maximum_invoice_set_number;
    }

    /**
     * @return int
     */
    public function getMaximumRefundDemandNumber(): ?int
    {
        return $this->maximum_refund_demand_number;
    }

    /**
     * @return int
     */
    public function getDesiredInvoiceNumber(): ?int
    {
        return $this->desired_invoice_number;
    }

    /**
     * @return int
     */
    public function getFileNumber(): ?int
    {
        return $this->file_number;
    }

    /**
     * @return bool
     */
    public function getInvoiceNumberRangeActivation(): ?bool
    {
        return $this->invoice_number_range_activation;
    }

    /**
     * @return int
     */
    public function getInvoiceNumberRangeStart(): ?int
    {
        return $this->invoice_number_range_start;
    }

    /**
     * @return int
     */
    public function getInvoiceNumberRangeEnd(): ?int
    {
        return $this->invoice_number_range_end;
    }
}
