<?php

namespace Ox\Interop\Hl7\V2\Handle\ObservationResultSet;

use Ox\Interop\Hl7\Exceptions\V2\CHL7v2ExceptionWarning;
use Ox\Interop\Hl7\V2\Handle\RecordObservationResultSet;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class CHL7v2RecordObservationResultSet
 * Record observation result set, message XML
 */
class HandleORU extends RecordObservationResultSet
{
    /** @var string[] */
    static $event_codes = ["R01"];

    /**
     * Get data nodes
     *
     * @return array Get nodes
     */
    function getContentNodes()
    {
        $data = [];

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $patient_results = $this->queryNodes("ORU_R01.PATIENT_RESULT", null, $varnull, true);

        foreach ($patient_results as $_patient_result) {
            // Patient
            $oru_patient               = $this->queryNode("ORU_R01.PATIENT", $_patient_result, $varnull);
            $PID                       = $this->queryNode("PID", $oru_patient, $data, true);
            $data["personIdentifiers"] = $this->getPersonIdentifiers("PID.3", $PID, $sender);

            // Venue
            $oru_visit = $this->queryNode("ORU_R01.VISIT", $oru_patient, $varnull);
            $PV1       = $this->queryNode("PV1", $oru_visit, $data, true);
            if ($PV1) {
                $data["admitIdentifiers"] = $this->getAdmitIdentifiers($PV1, $sender);
            }

            // Observations
            $order_observations   = $this->queryNodes("ORU_R01.ORDER_OBSERVATION", $_patient_result, $varnull);
            $data["observations"] = [];
            foreach ($order_observations as $_order_observation) {
                $tmp = [];
                // OBXs
                $oru_observations = $this->queryNodes("ORU_R01.OBSERVATION", $_order_observation, $varnull);
                foreach ($oru_observations as $_oru_observation) {
                    $this->queryNodes("OBX", $_oru_observation, $tmp, true);
                }

                // OBR - on récupère uniquement le OBR concernant le OBX
                $this->queryNode("OBR", $_order_observation, $tmp);

                // ORC - Common Order [0-*]
                $this->queryNode("ORC", $_order_observation, $tmp);

                if ($tmp) {
                    $data["observations"][] = $tmp;
                }
            }
        }

        return $data;
    }

    protected function handleObservations(array $data): void
    {
        /** @var array $observation */
        foreach ($data["observations"] as $key => $observation) {
            try {
                $bag = new ParameterBag($observation);
                $bag->set('OBSERVATION.index', (int)$key);
                $this->getObjectObservationHandle()->handle($bag);
            } catch (CHL7v2ExceptionWarning $warning) {
                $this->codes[] = $warning->getWarning();
            }
        }
    }
}
