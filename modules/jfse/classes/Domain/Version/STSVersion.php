<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class STSVersion extends AbstractEntity
{
    /** @var string|null */
    protected $debug;

    /** @var Detail[] */
    protected $details;

    public function getDetails(): array
    {
        return $this->details;
    }

}
