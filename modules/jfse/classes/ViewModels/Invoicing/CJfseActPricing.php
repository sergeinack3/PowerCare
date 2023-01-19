<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Invoicing;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CJfseActPricing extends CJfseViewModel
{
    /** @var float */
    public $exceeding_amount;

    /** @var float */
    public $total_amount;

    /** @var int */
    public $additional_charge;

    /** @var bool */
    public $exceptional_reimbursement;

    /** @var float */
    public $unit_price;

    /** @var float */
    public $reimbursement_base;

    /** @var float */
    public $referential_price;

    /** @var int */
    public $rate;

    /** @var float */
    public $invoice_total;

    /** @var float */
    public $total_amo;

    /** @var float */
    public $total_insured;

    /** @var float */
    public $total_amc;

    /** @var float */
    public $owe_amo;

    /** @var float */
    public $owe_amc;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['exceeding_amount'] = 'currency';
        $props['total_amount'] = 'currency';
        $props['additional_charge'] = 'currency';
        $props['exceptional_reimbursement'] = 'currency';
        $props['unit_price'] = 'currency';
        $props['reimbursement_base'] = 'currency';
        $props['referential_price'] = 'currency';
        $props['rate'] = 'num';
        $props['invoice_total'] = 'currency';
        $props['total_amo'] = 'currency';
        $props['total_insured'] = 'currency';
        $props['total_amc'] = 'currency';
        $props['owe_amo'] = 'currency';
        $props['owe_amc'] = 'currency';

        return $props;
    }
}
