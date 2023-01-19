<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Formula;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Class FormulaOperand
 *
 * @package Ox\Mediboard\Jfse\Domain\Formula
 */
final class FormulaOperand extends AbstractEntity
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
}
