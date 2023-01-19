<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class InvoicingTla extends AbstractEntity
{
    /** @var int */
    protected $group;

    /** @var DateTimeImmutable */
    protected $vital_card_reading_date;

    /** @var string */
    protected $type;

    /** @var int */
    protected $third_party_accident;

    /** @var DateTimeImmutable */
    protected $third_party_accident_date;

    /** @var DateTimeImmutable */
    protected $maternity_date;

    /** @var DateTimeImmutable */
    protected $work_accident_date;

    /** @var string */
    protected $work_accident_number;

    /** @var string */
    protected $amc_zone;
}
