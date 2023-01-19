<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Invoicing;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class CRuleForcing
 * @package Ox\Mediboard\Jfse\ViewModels\Invoicing
 */
class CRuleForcing extends CJfseViewModel
{
    /** @var int */
    public $serial_id;

    /** @var int */
    public $forcing_type;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['serial_id']    = 'num';
        $props['forcing_type'] = 'num';

        return $props;
    }
}
