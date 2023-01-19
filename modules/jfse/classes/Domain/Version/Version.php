<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class Version extends AbstractEntity
{
    /** @var string */
    protected $organisations;

    /** @var string */
    protected $cdc;

    /** @var DateTimeImmutable */
    protected $cdc_date;

    /** @var DateTimeImmutable */
    protected $prices_date;

    /** @var string */
    protected $mail;

    /** @var string */
    protected $server_version;

    /** @var string */
    protected $daemon_version;

    /** @var string */
    protected $ccam_version;

    /** @var string */
    protected $base_api_version;

    public function getOrganisations(): string
    {
        return $this->organisations;
    }

    public function getCdc(): string
    {
        return $this->cdc;
    }

    public function getCdcDateString(): string
    {
        return $this->cdc_date->format('Y-m-d');
    }

    public function getPricesDateString(): string
    {
        return $this->prices_date->format('Y-m-d');
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function getServerVersion(): string
    {
        return $this->server_version;
    }

    public function getDaemonVersion(): string
    {
        return $this->daemon_version;
    }

    public function getCcamVersion(): string
    {
        return $this->ccam_version;
    }

    public function getBaseApiVersion(): string
    {
        return $this->base_api_version;
    }


}
