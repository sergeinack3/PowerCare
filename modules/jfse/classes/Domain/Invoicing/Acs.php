<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Class Acs
 *
 * @package Ox\Mediboard\Jfse\Domain\Invoicing
 */
final class Acs extends AbstractEntity
{
    /** @var AcsManagementModeEnum */
    protected $management_mode;

    /** @var AcsContractTypeEnum */
    protected $contract_type;

    /**
     * @return AcsManagementModeEnum
     */
    public function getManagementMode(): AcsManagementModeEnum
    {
        return $this->management_mode;
    }

    /**
     * @return AcsContractTypeEnum
     */
    public function getContractType(): AcsContractTypeEnum
    {
        return $this->contract_type;
    }
}
