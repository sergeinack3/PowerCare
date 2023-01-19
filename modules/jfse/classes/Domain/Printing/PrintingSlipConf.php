<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Printing;

use DateTimeImmutable;

/**
 * Printing slip properties
 */
class PrintingSlipConf
{
    /** @var int */
    private $mode;

    /** @var bool */
    private $degrade;

    /** @var DateTimeImmutable */
    private $date_min;

    /** @var DateTimeImmutable */
    private $date_max;

    /** @var int[] */
    private $batch;

    /** @var int[] */
    private $files;

    public function __construct(int $mode, bool $degrade)
    {
        $this->mode    = $mode;
        $this->degrade = $degrade;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function getDegrade(): bool
    {
        return $this->degrade;
    }

    public function getDateMin(): ?DateTimeImmutable
    {
        return $this->date_min;
    }

    public function setDateMin(DateTimeImmutable $date_min): void
    {
        $this->date_min = $date_min;
    }

    public function getDateMax(): ?DateTimeImmutable
    {
        return $this->date_max;
    }

    public function setDateMax(DateTimeImmutable $date_max): void
    {
        $this->date_max = $date_max;
    }

    public function getBatch(): ?array
    {
        return $this->batch;
    }

    public function setBatch(array $batch): void
    {
        $this->batch = $batch;
    }

    public function getFiles(): ?array
    {
        return $this->files;
    }

    public function setFiles(array $files): void
    {
        $this->files = $files;
    }
}
