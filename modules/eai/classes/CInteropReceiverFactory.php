<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Interop\Dmp\CReceiverDMP;
use Ox\Interop\Fhir\Actors\CReceiverFHIR;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Interop\Hl7\CReceiverHL7v3;
use Ox\Interop\Hprimxml\CDestinataireHprim;

/**
 * Class CInteropReceiver
 * Interoperability Receiver
 */
class CInteropReceiverFactory extends CInteropActorFactory
{
    /** @var string */
    protected const MAIN_ACTOR = CInteropReceiver::class;

    /**
     * Get DMP receiver
     *
     * @return CReceiverDMP|CInteropReceiver
     * @throws CEAIException
     */
    public function makeDMP(): CReceiverDMP
    {
        return $this::make(CReceiverDMP::class);
    }

    /**
     * Get HL7v2 receiver
     *
     * @param string|null $receiver_type Receiver type
     *
     * @return CReceiverHL7v2|CInteropReceiver
     * @throws CEAIException
     */
    public function makeHL7v2(?string $receiver_type = null): CReceiverHL7v2
    {
        return $this->make(CReceiverHL7v2::class, $receiver_type);
    }

    /**
     * Get HL7v3 receiver
     *
     * @param string|null $receiver_type Receiver type
     *
     * @return CReceiverHL7v3|CInteropReceiver
     * @throws CEAIException
     */
    public function makeHL7v3(?string $receiver_type = null): CReceiverHL7v3
    {
        return $this->make(CReceiverHL7v3::class, $receiver_type);
    }

    /**
     * Get FHIR receiver
     *
     * @param string|null $receiver_type Receiver type
     *
     * @return CReceiverFHIR|CInteropReceiver
     * @throws CEAIException
     */
    public function makeFHIR(?string $receiver_type = null): CReceiverFHIR
    {
        return $this->make(CReceiverFHIR::class, $receiver_type);
    }

    /**
     * Get H'XML receiver
     *
     * @param string|null $receiver_type Receiver type
     *
     * @return CDestinataireHprim|CInteropReceiver
     * @throws CEAIException
     */
    public function makeHprimXML(?string $receiver_type = null): CDestinataireHprim
    {
        return $this->make(CDestinataireHprim::class, $receiver_type);
    }
}
