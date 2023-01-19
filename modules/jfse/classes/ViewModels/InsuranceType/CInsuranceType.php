<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\InsuranceType;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class CInsuranceType
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
abstract class CInsuranceType extends CJfseViewModel
{
    /** @var string */
    public $invoice_id;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['invoice_id'] = 'str notNull';

        return $props;
    }
}
