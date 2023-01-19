<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Formula;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

final class Parameter extends AbstractEntity
{
    public const TYPE_PERCENTAGE = 'P';
    public const TYPE_MONEY = 'M';

    /** @var string */
    protected $number;

    /** @var string */
    protected $label;

    /** @var string */
    protected $type;

    /** @var float */
    protected $value;

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }
}
