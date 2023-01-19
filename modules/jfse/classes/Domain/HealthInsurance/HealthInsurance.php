<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\HealthInsurance;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Class HealthInsurance
 *
 * @package Ox\Mediboard\Jfse\Domain\HealthInsurance
 */
final class HealthInsurance extends AbstractEntity
{
    /** @var string */
    protected $code;

    /** @var string */
    protected $name;

    /** @var int */
    protected $type_of_organization;

    public const SEARCH_MODE_START_WITH = 0;
    public const SEARCH_MODE_CONTAINS   = 1;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTypeOfOrganization(): string
    {
        return $this->type_of_organization;
    }
}
