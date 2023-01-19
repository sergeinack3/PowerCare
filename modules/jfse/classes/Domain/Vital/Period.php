<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use DateTimeImmutable;
use JsonSerializable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class Period extends AbstractEntity implements JsonSerializable
{
    /** @var int */
    protected $group;

    /** @var DateTimeImmutable|null */
    protected $begin_date;

    /** @var DateTimeImmutable|null */
    protected $end_date;

    /**
     * @return int
     */
    public function getGroup(): ?int
    {
        return $this->group;
    }

    public function getBeginDate(): ?DateTimeImmutable
    {
        return $this->begin_date;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->end_date;
    }

    public function jsonSerialize(): array
    {
        return [
            "begin_date" => ($this->begin_date) ? $this->begin_date->format('Y-m-d') : "",
            "end_date" => ($this->end_date) ? $this->end_date->format('Y-m-d') : ""
        ];
    }
}
