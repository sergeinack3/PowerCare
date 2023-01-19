<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle\ObservationResultSet;

use DOMNode;
use Ox\Interop\Hl7\V2\Handle\RecordObservationResultSet;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class HandleORSOBX extends HandleORS
{
    /** @var DOMNode */
    protected $OBX;

    /** @var DOMNode[] */
    protected $observation;

    /** @var CObservationResultSet */
    protected $observation_result_set;

    /** @var int */
    protected $OBX_index;

    /**
     * HandleORSFilesRPED constructor.
     *
     * @param RecordObservationResultSet $message
     * @param DOMNode[]                  $observation
     * @param DOMNode                    $OBX
     */
    public function __construct(RecordObservationResultSet $message, ParameterBag $observation, DOMNode $OBX)
    {
        parent::__construct($message);

        $this->observation = $observation;
        $this->OBX         = $OBX;
    }

    /**
     * @param ParameterBag $bag
     */
    public function handle(ParameterBag $bag): void
    {
        parent::handle($bag);

        // Keep Observation Result Set
        if ($observation_result_set = $bag->get('observation_result_set')) {
            $this->observation_result_set = $observation_result_set;
        }

        // Keep OBX index
        if (($OBX_index = $bag->get('OBX.index')) !== null) {
            $this->OBX_index = $OBX_index;
        }
    }

    /**
     * @return string
     */
    protected function getPosition(): string
    {
        return parent::getPosition() . "/OBX[$this->OBX_index]";
    }
}
