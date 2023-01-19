<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Class RuleForcing
 *
 * @package Ox\Mediboard\Jfse\Domain\Invoicing
 */
final class RuleForcing extends AbstractEntity
{
    public const STANDARD_FORCING         = 0;
    public const COMPLETE_CONTROL_FORCING = 1;

    /** @var int */
    protected $serial_id;

    /** @var int */
    protected $forcing_type;

    public function getSerialId(): int
    {
        return $this->serial_id;
    }

    public function getForcingType(): ?int
    {
        return $this->forcing_type;
    }
}
