<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CMbObjectSpec;

/**
 * Description
 */
class CLaboratoireBacterio extends CLaboratoire
{
    /** @var int Primary key */
    public $laboratoire_bacterio_id;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'laboratoire_bacterio';
        $spec->key   = 'laboratoire_bacterio_id';

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps(): array
    {
        $props             = parent::getProps();
        $props['group_id'] .= ' back|labos_bacterio';

        return $props;
    }
}
