<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\RefundCancelRequest;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Class RefundCancelRequestDetails
 *
 * @package Ox\Mediboard\Jfse\Domain\RefundCancelRequest
 */
class RefundCancelRequestDetails extends AbstractEntity
{
    /** @var string */
    protected $dre_number;

    /** @var string */
    protected $invoice_id;

    /** @var string */
    protected $invoice_number;

    /** @var string */
    protected $beneficiary_last_name;

    /** @var string */
    protected $beneficiary_first_name;

    /** @var string */
    protected $securisation;

    /** @var string */
    protected $ps_name;

    /** @var string */
    protected $date_elaboration;

    /**
     * @return string
     */
    public function getDreNumber(): string
    {
        return $this->dre_number;
    }

    /**
     * @return int
     */
    public function getInvoiceId(): string
    {
        return $this->invoice_id;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber(): string
    {
        return $this->invoice_number;
    }

    /**
     * @return string
     */
    public function getBeneficiaryLastName(): string
    {
        return $this->beneficiary_last_name;
    }

    /**
     * @return string
     */
    public function getBeneficiaryFirstName(): string
    {
        return $this->beneficiary_first_name;
    }

    /**
     * @return string
     */
    public function getSecurisation(): string
    {
        return $this->securisation;
    }

    /**
     * @return string
     */
    public function getPsName(): string
    {
        return $this->ps_name;
    }

    /**
     * @return string
     */
    public function getDateElaboration(): string
    {
        return $this->date_elaboration;
    }
}
