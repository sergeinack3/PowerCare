<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class Software extends AbstractEntity
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $version_number;

    /** @var DateTimeImmutable */
    protected $date_time;

    /** @var string */
    protected $checksum;

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersionNumber(): string
    {
        return $this->version_number;
    }

    public function getDateTimeString(): string
    {
        return $this->date_time->format('Y-m-d H:i:s');
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }
}
