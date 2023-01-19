<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle\ObservationResultSet;

use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\Handle;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class HandleORSObservationResults
 *
 * @package Ox\Interop\Hl7\V2\Handle\ObservationResultSet
 */
abstract class HandleORSObservationResults extends HandleORSOBX
{
    /**
     * @param ParameterBag $bag
     *
     * @throws CHL7v2Exception
     */
    public function handle(ParameterBag $bag): void
    {
        parent::handle($bag);
    }
}
