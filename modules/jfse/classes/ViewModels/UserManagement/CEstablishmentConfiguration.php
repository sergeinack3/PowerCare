<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\UserManagement;

use Ox\Mediboard\Jfse\Domain\UserManagement\EmployeeCard;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CEstablishmentConfiguration extends CJfseViewModel
{
    /** @var int */
    public $invoice_number;

    /** @var int */
    public $invoice_set_number;

    /** @var int */
    public $refund_demand_number;

    /** @var int */
    public $maximum_invoice_set_number;

    /** @var int */
    public $desired_invoice_number;

    /** @var int */
    public $file_number;

    /** @var bool */
    public $invoice_number_range_activation;

    /** @var int */
    public $invoice_number_range_start;

    /** @var int */
    public $invoice_number_range_end;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['invoice_number']                  = 'num';
        $props['invoice_set_number']              = 'num';
        $props['refund_demand_number']            = 'num';
        $props['maximum_invoice_set_number']      = 'num';
        $props['maximum_refund_demand_number']    = 'num';
        $props['desired_invoice_number']          = 'num';
        $props['file_number']                     = 'num';
        $props['invoice_number_range_activation'] = 'bool default|0';
        $props['invoice_number_range_start']      = 'num';
        $props['invoice_number_range_end']        = 'num';

        return $props;
    }
}
