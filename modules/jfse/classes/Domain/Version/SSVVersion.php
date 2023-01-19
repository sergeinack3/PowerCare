<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class SSVVersion extends AbstractEntity
{
    /** @var string|null */
    protected $debug;

    /** @var Computer */
    protected $computer;

    /** @var CardReaderConfig[] */
    protected $card_reader_configs;

    /** @var PCSCReader[] */
    protected $pcsc_readers;

    /** @var SesamVitaleComponent[] */
    protected $sesam_vitale_components;

    public function getDebug(): ?string
    {
        return $this->debug;
    }

    public function getComputer(): Computer
    {
        return $this->computer;
    }

    public function getCardReaderConfigs(): array
    {
        return $this->card_reader_configs;
    }

    public function getPcscReaders(): array
    {
        return $this->pcsc_readers;
    }

    public function getSesamVitaleComponents(): array
    {
        return $this->sesam_vitale_components;
    }
}
