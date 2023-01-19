<?php

/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Handle\ORU;

use Ox\Mediboard\ObservationResult\CObservationResultSet;

abstract class HandleObservationResult extends Handle
{
    /** @var CObservationResultSet */
    protected $observation_result_set;

    /**
     * @return CObservationResultSet
     */
    public function getObservationResultSet(): CObservationResultSet
    {
        return $this->observation_result_set;
    }
}
