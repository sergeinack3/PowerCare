<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Substitute;

use DateTime;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Represents a substitution session of a substitute practitioner
 *
 * @package Ox\Mediboard\Jfse\Domain\CPS
 */
final class Session extends AbstractEntity
{
    /** @var int */
    protected $id;

    /** @var DateTime */
    protected $begin_date;

    /** @var DateTime */
    protected $end_date;

    /** @var bool */
    protected $monday;

    /** @var bool */
    protected $tuesday;

    /** @var bool */
    protected $wednesday;

    /** @var bool */
    protected $thursday;

    /** @var bool */
    protected $friday;

    /** @var bool */
    protected $saturday;

    /** @var bool */
    protected $sunday;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getBeginDate(): ?DateTime
    {
        return $this->begin_date;
    }

    /**
     * @return DateTime
     */
    public function getEndDate(): ?DateTime
    {
        return $this->end_date;
    }

    /**
     * @return bool
     */
    public function isMonday(): ?bool
    {
        return $this->monday;
    }

    /**
     * @return bool
     */
    public function isTuesday(): ?bool
    {
        return $this->tuesday;
    }

    /**
     * @return bool
     */
    public function isWednesday(): ?bool
    {
        return $this->wednesday;
    }

    /**
     * @return bool
     */
    public function isThursday(): ?bool
    {
        return $this->thursday;
    }

    /**
     * @return bool
     */
    public function isFriday(): ?bool
    {
        return $this->friday;
    }

    /**
     * @return bool
     */
    public function isSaturday(): ?bool
    {
        return $this->saturday;
    }

    /**
     * @return bool
     */
    public function isSunday(): ?bool
    {
        return $this->sunday;
    }
}
