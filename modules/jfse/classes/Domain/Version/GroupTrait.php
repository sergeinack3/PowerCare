<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

trait GroupTrait
{
    /** @var string */
    protected $group;

    public function getGroup(): string
    {
        return $this->group;
    }
}
