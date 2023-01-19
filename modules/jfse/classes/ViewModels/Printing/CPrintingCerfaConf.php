<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Printing;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Configuration for printing cerfa documents
 */
class CPrintingCerfaConf extends CJfseViewModel
{
    /** @var string|null */
    public $invoice_id;

    /** @var int|null */
    public $invoice_number;

    /** @var bool */
    public $duplicate;

    /** @var bool */
    public $use_signature;

    /** @var bool */
    public $use_background;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['invoice_id']     = 'str';
        $props['invoice_number'] = 'num';
        $props['duplicate']      = 'bool';
        $props['use_signature']  = 'bool';
        $props['use_background'] = 'bool';

        return $props;
    }
}
