<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Invoicing;

use Ox\Mediboard\Jfse\Domain\Invoicing\AcsContractTypeEnum;
use Ox\Mediboard\Jfse\Domain\Invoicing\AcsManagementModeEnum;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CAcs extends CJfseViewModel
{
    /** @var string */
    public $management_mode;

    /** @var string */
    public $contract_type;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["management_mode"] = AcsManagementModeEnum::getProp();
        $props["contract_type"] = AcsContractTypeEnum::getProp();

        return $props;
    }
}
