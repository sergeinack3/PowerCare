<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class CardReaderConfig extends AbstractEntity
{
    use GroupTrait;

    /** @var string */
    protected $reader_constructor_name;

    /** @var string */
    protected $reader_type;

    /** @var string */
    protected $serial_number;

    /** @var string - Reader's Operating System */
    protected $os_reader;

    /** @var int */
    protected $reader_amount_softwares;

    /** @var Software[] */
    protected $softwares;

    public function getReaderConstructorName(): string
    {
        return $this->reader_constructor_name;
    }

    public function getReaderType(): string
    {
        return $this->reader_type;
    }

    public function getSerialNumber(): string
    {
        return $this->serial_number;
    }

    public function getOsReader(): string
    {
        return $this->os_reader;
    }

    public function getReaderAmountSoftwares(): int
    {
        return $this->reader_amount_softwares;
    }

    public function getSoftwares(): array
    {
        return $this->softwares;
    }
}
