<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\PrescribingPhysician;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class PhysicianType extends AbstractEntity
{
    /** @var int */
    protected $code;

    /** @var string */
    protected $label;

    public function getCode(): int
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
