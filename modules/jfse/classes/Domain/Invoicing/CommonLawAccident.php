<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use DateTime;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

final class CommonLawAccident extends AbstractEntity
{
    /** @var bool */
    protected $common_law_accident;

    /** @var DateTime */
    protected $date;

    /**
     * @return bool
     */
    public function getCommonLawAccident(): ?bool
    {
        return $this->common_law_accident;
    }

    /**
     * @return DateTime
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getDateString(): ?string
    {
        return $this->date ? $this->date->format('Ymd') : null;
    }
}
