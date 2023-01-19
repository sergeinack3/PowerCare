<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\RefundCancelRequest;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class CRefundRequestCancel
 *
 * @package Ox\Mediboard\Jfse\ViewModels\RefundCancelRequest
 */
class CRefundCancelRequest extends CJfseViewModel
{
    /** @var string */
    public $type;

    /** @var int */
    public $jfse_id;

    /** @var string */
    public $dre_lot_number;

    /** @var string */
    public $fse_lot_number;

    /** @var string */
    public $invoice_number;

    /** @var string */
    public $invoice_id;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props["type"]           = 'str';
        $props["jfse_id"]        = 'num';
        $props["dre_lot_number"] = 'str';
        $props["fse_lot_number"] = 'str';
        $props["invoice_number"] = 'str';
        $props["invoice_id"]     = 'str';

        return $props;
    }
}
