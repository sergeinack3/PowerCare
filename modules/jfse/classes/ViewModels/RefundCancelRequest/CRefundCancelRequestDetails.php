<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\RefundCancelRequest;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class CRefundRequestCancelDetails
 *
 * @package Ox\Mediboard\Jfse\ViewModels\RefundCancelRequest
 */
class CRefundCancelRequestDetails extends CJfseViewModel
{
    /** @var string */
    public $dre_number;

    /** @var string */
    public $invoice_id;

    /** @var string */
    public $invoice_number;

    /** @var string */
    public $beneficiary_last_name;

    /** @var string */
    public $beneficiary_first_name;

    /** @var string */
    public $securisation;

    /** @var string */
    public $ps_name;

    /** @var string */
    public $date_elaboration;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props["dre_number"]             = 'str';
        $props["invoice_id"]             = 'str';
        $props["invoice_number"]         = 'str';
        $props["beneficiary_last_name"]  = 'str';
        $props["beneficiary_first_name"] = 'str';
        $props["securisation"]           = 'num min|1 max|5';
        $props["ps_name"]                = 'str';
        $props["date_elaboration"]       = 'str';

        return $props;
    }
}
