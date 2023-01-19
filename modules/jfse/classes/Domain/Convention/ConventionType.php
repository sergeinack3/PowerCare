<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Convention;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Class ConventionType
 *
 * @package Ox\Mediboard\Jfse\Domain\Convention
 */
final class ConventionType extends AbstractEntity
{
    /** @var string */
    protected $code;

    /** @var string */
    protected $label;

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
