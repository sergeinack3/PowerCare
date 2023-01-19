<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Adri;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCard;

final class Adri extends AbstractEntity
{
    /** @var DateTimeImmutable */
    protected $response_date;

    /** @var VitalCard */
    protected $vital_card;

    public function getVitalCard(): VitalCard
    {
        return $this->vital_card;
    }
}
