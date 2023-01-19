<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\InsuranceType;

use JsonSerializable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Class InsuranceType
 *
 * @package Ox\Mediboard\Jfse\Domain\InsuranceType
 */
final class InsuranceType extends AbstractEntity implements JsonSerializable
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
            'code'    => $this->code,
            'label' => $this->label,
        ];
    }
}
