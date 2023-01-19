<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Convention;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Class Correspondence
 *
 * @package Ox\Mediboard\Jfse\Domain\Convention
 */
final class Correspondence extends AbstractEntity
{
    /** @var int */
    protected $correspondence_id;
    /** @var string */
    protected $health_insurance_number;
    /** @var string */
    protected $regime_code;
    /** @var string */
    protected $amc_number;
    /** @var string */
    protected $amc_label;
    /** @var int */
    protected $group_id;

    public function getCorrespondenceId(): int
    {
        return $this->correspondence_id;
    }

    public function getHealthInsuranceNumber(): string
    {
        return $this->health_insurance_number;
    }

    public function getRegimeCode(): string
    {
        return $this->regime_code;
    }

    public function getAmcNumber(): string
    {
        return $this->amc_number;
    }

    public function getAmcLabel(): string
    {
        return $this->amc_label;
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

}
