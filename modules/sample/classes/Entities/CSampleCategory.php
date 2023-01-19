<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Entities;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;

/**
 * Representation of a category for movies.
 */
class CSampleCategory extends CMbObject
{
    public const RESOURCE_TYPE = 'sample_category';

    /** @var int */
    public $sample_category_id;

    /** @var string */
    public $name;

    /** @var string */
    public $color;

    /** @var bool */
    public $active;

    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "sample_category";
        $spec->key   = "sample_category_id";

        $spec->uniques['name'] = ['name'];

        return $spec;
    }

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['name']   = 'str notNull fieldset|default';
        $props['color']  = 'color fieldset|default';
        $props['active'] = 'bool default|1 fieldset|default';

        return $props;
    }
}
