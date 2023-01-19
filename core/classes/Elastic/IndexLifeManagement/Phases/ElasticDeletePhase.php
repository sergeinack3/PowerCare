<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\IndexLifeManagement\Phases;

/**
 * This class is the representation of the ILM Delete phase.
 */
class ElasticDeletePhase extends AbstractElasticPhase
{
    public function build(): array
    {
        $array                                                    = parent::build();
        $array["actions"]["delete"]["delete_searchable_snapshot"] = true;

        return $array;
    }
}
