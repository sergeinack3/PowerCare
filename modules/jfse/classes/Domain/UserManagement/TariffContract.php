<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\UserManagement;

use JsonSerializable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class TariffContract extends AbstractEntity implements JsonSerializable
{
    /** @var int */
    protected $code;
    /** @var string */
    protected $label;

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'code'  => $this->getCode(),
            'label' => $this->getLabel(),
        ];
    }
}
