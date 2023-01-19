<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\InsuranceType;

use DateTimeImmutable;

/**
 * Class CMaternityInsuranceType
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
final class MaternityInsurance extends AbstractInsurance
{
    public const CODE = 1;

    /** @var DateTimeImmutable */
    protected $date;
    /** @var bool */
    protected $force_exoneration;

    /**
     * @return DateTimeImmutable
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return bool
     */
    public function getForceExoneration(): ?bool
    {
        return $this->force_exoneration;
    }
}
