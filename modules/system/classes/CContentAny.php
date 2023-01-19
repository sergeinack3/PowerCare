<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbString;

/**
 * Any content
 */
class CContentAny extends CMbObject
{
    /** @var int */
    public $content_id;

    // DB Fields
    /** @var string */
    public $content;
    /** @var int */
    public $import_id;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'content_any';
        $spec->key   = 'content_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props              = parent::getProps();
        $props["content"]   = "text show|0";
        $props["import_id"] = "num";

        return $props;
    }

    public function updatePlainFields(): void
    {
        parent::updatePlainFields();

        if (!$this->content) {
            return;
        }

        if (CMbString::isHtml($this->content) && !str_starts_with($this->content, '<?xml')) {
            $this->content = CMbString::purifyHTML($this->content);
        }
    }
}
