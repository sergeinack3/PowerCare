<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class ApiVersion extends AbstractEntity
{
    /** @var SSVVersion */
    protected $ssv;

    /** @var SRTVersion */
    protected $srt;

    /** @var STSVersion */
    protected $sts;

    public function getSsv(): SSVVersion
    {
        return $this->ssv;
    }

    public function getSrt(): SRTVersion
    {
        return $this->srt;
    }

    public function getSts(): STSVersion
    {
        return $this->sts;
    }
}
