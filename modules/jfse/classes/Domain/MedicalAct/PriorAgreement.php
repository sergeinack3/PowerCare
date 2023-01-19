<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\MedicalAct;

use DateTime;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class PriorAgreement extends AbstractEntity
{
    /** @var int */
    protected $value;

    /** @var DateTime */
    protected $send_date;

    /**
     * @return int
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * @return DateTime
     */
    public function getSendDate(): ?DateTime
    {
        return $this->send_date;
    }
}
