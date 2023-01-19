<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\PrescribingPhysician;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class PhysicianSpeciality extends AbstractEntity
{
    /** @var string */
    protected $code;

    /** @var string */
    protected $label;

    /** @var string */
    protected $family;

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getFamily(): string
    {
        return $this->family;
    }
}
