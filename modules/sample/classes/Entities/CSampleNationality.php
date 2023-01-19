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
 * Reprensentation of a nationality for persons (CSamplePerson).
 */
class CSampleNationality extends CMbObject
{
    public const RESOURCE_TYPE = 'sample_nationality';

    /** @var int */
    public $sample_nationality_id;

    /** @var string */
    public $name;

    /** @var string */
    public $code;

    /** @var string */
    public $flag;

    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "sample_nationality";
        $spec->key   = "sample_nationality_id";

        $spec->uniques['code'] = ['code'];

        return $spec;
    }

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['name'] = 'str notNull fieldset|default';
        $props['code'] = 'str maxLength|5 notNull fieldset|default';
        $props['flag'] = 'code fieldset|default';

        return $props;
    }
}
