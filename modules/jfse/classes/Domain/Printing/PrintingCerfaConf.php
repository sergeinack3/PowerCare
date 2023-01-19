<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Printing;

/**
 * Printing cerfa properties
 */
class PrintingCerfaConf
{
    /** @var int|null */
    private $invoice_number;

    /** @var string|null */
    private $invoice_id;

    /** @var bool */
    private $duplicate;

    /** @var bool */
    private $user_signature;

    /** @var bool */
    private $use_background;

    public function __construct(bool $duplicate, ?bool $user_signature = null, ?bool $use_background = null)
    {
        $this->duplicate      = $duplicate;
        $this->user_signature = $user_signature;
        $this->use_background = $use_background;
    }

    public function getInvoiceNumber(): ?int
    {
        return $this->invoice_number;
    }

    public function setInvoiceNumber(?int $invoice_number): void
    {
        $this->invoice_number = $invoice_number;
    }

    public function getInvoiceId(): ?int
    {
        return $this->invoice_id;
    }

    public function setInvoiceId(?string $invoice_id): void
    {
        $this->invoice_id = $invoice_id;
    }

    public function getDuplicate(): bool
    {
        return $this->duplicate;
    }


    public function getUserSignature(): ?bool
    {
        return $this->user_signature;
    }


    public function getUseBackground(): ?bool
    {
        return $this->use_background;
    }
}
