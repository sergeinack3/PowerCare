<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\DataModels;

use Ox\Core\CMbObjectSpec;

/**
 * Link the payment to an invoice
 */
final class CJfseInvoicePayment extends CJfseDataModel
{
    /** @var ?int Primary key */
    public ?int $jfse_invoice_payment_id;

    /** @var ?int */
    public ?int $invoice_id;

    /** @var ?int */
    public ?int $payment_id;

    /** @var ?float */
    public ?float $amount_amo;

    /** @var ?float */
    public ?float $amount_amc;

    /** @var ?float */
    public ?float $total;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'jfse_invoice_payments';
        $spec->key   = 'jfse_invoice_payment_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['invoice_id'] = 'ref class|CJfseInvoice notNull back|payments';
        $props['payment_id'] = 'ref class|CJfsePayment notNull back|invoice_payments';
        $props['amount_amo'] = 'currency';
        $props['amount_amc'] = 'currency';
        $props['total']      = 'currency';

        return $props;
    }
}
