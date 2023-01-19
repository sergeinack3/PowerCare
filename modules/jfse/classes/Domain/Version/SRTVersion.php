<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class SRTVersion extends AbstractEntity
{
    use GroupTrait;

    /** @var string|null */
    protected $debug;

    /** @var string */
    protected $referential;

    /** @var string */
    protected $referential_server;

    /** @var string */
    protected $ccam_db;

    /** @var string|null */
    protected $ccam_db_server;

    /** @var DateTimeImmutable|null */
    protected $modification_date;

    /** @var string */
    protected $referential_variant;

    /** @var string */
    protected $comment;

    /** @var string */
    protected $referential_revision;

    /** @var string */
    protected $software_version;

    public function getReferential(): string
    {
        return $this->referential;
    }

    public function getReferentialServer(): string
    {
        return $this->referential_server;
    }

    public function getCcamDb(): string
    {
        return $this->ccam_db;
    }

    public function getCcamDbServer(): ?string
    {
        return $this->ccam_db_server;
    }

    public function getModificationDateString(): string
    {
        if ($this->modification_date) {
            return $this->modification_date->format('Y-m-d');
        }

        return '';
    }

    public function getReferentialVariant(): string
    {
        return $this->referential_variant;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getReferentialRevision(): string
    {
        return $this->referential_revision;
    }

    public function getSoftwareVersion(): string
    {
        return $this->software_version;
    }
}
