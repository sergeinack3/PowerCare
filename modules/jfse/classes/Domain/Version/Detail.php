<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class Detail extends AbstractEntity
{
    use GroupTrait;

    /** @var string */
    protected $module_identification;

    /** @var string */
    protected $module_identification_label;

    /** @var string */
    protected $module_version;

    /** @var string */
    protected $external_tables_version;

    /** @var string */
    protected $variant;

    /** @var string */
    protected $comment;

    public function getModuleIdentification(): string
    {
        return $this->module_identification;
    }

    public function getModuleIdentificationLabel(): string
    {
        return $this->module_identification_label;
    }

    public function getModuleVersion(): string
    {
        return $this->module_version;
    }

    public function getExternalTablesVersion(): string
    {
        return $this->external_tables_version;
    }

    public function getVariant(): string
    {
        return $this->variant;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
