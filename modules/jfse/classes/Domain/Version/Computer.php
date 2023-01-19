<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class Computer extends AbstractEntity
{
    use GroupTrait;

    /** @var string */
    protected $ssv_version;

    /** @var string */
    protected $galss_version;

    /** @var string */
    protected $pss_version;

    public function getSsvVersion(): string
    {
        return $this->ssv_version;
    }

    public function getGalssVersion(): string
    {
        return $this->galss_version;
    }

    public function getPssVersion(): string
    {
        return $this->pss_version;
    }
}
