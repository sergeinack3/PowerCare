<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class SesamVitaleComponent extends AbstractEntity
{
    use GroupTrait;

    /** @var int */
    protected $id;

    /** @var string */
    protected $label;

    /** @var string */
    protected $version_number;

    public function getId(): int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getVersionNumber(): string
    {
        return $this->version_number;
    }
}
