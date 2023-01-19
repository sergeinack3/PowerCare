<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class PCSCReader extends AbstractEntity
{
    use GroupTrait;

    /** @var string */
    protected $name;

    /** @var string */
    protected $card_type;

    public function getName(): string
    {
        return $this->name;
    }

    public function getCardType(): string
    {
        return $this->card_type;
    }
}
