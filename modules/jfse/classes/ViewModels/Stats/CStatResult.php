<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Stats;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CStatResult extends CJfseViewModel
{
    /** @var int */
    public $amount_days_last_transmission;

    /** @var string */
    public $date_last_transmission;

    /** @var int */
    public $amount_invoices_pending_transmission;

    /** @var float */
    public $total_invoices_rejected;

    /** @var int */
    public $amount_invoices;

    /** @var float */
    public $total_invoices;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["amount_days_last_transmission"]        = "num";
        $props["date_last_transmission"]               = "date";
        $props["amount_invoices_pending_transmission"] = "num";
        $props["total_invoices_rejected"]              = "num";
        $props["amount_invoices"]                      = "num";
        $props["total_invoices"]                       = "num";

        return $props;
    }
}
