<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\RefundCancelRequest;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Class RefundCancelRequest
 *
 * @package Ox\Mediboard\Jfse\Domain\RefundCancelRequest
 */
class RefundCancelRequest extends AbstractEntity
{
    /** @var string */
    protected $type;

    /** @var int */
    protected $jfse_id;

    /** @var string */
    protected $dre_lot_number;

    /** @var string */
    protected $fse_lot_number;

    /** @var string */
    protected $invoice_number;

    /** @var string */
    protected $invoice_id;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getJfseId(): int
    {
        return $this->jfse_id;
    }

    /**
     * @return string
     */
    public function getDreLotNumber(): string
    {
        return $this->dre_lot_number;
    }

    /**
     * @return string
     */
    public function getFseLotNumber(): string
    {
        return $this->fse_lot_number;
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
    public function getInvoiceId(): string
    {
        return $this->invoice_id;
    }
}
