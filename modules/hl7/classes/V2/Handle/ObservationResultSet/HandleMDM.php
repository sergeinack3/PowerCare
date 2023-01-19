<?php

namespace Ox\Interop\Hl7\V2\Handle\ObservationResultSet;

use Ox\Core\CMbArray;
use Ox\Interop\Hl7\Exceptions\V2\CHL7v2ExceptionWarning;
use Ox\Interop\Hl7\V2\Handle\RecordObservationResultSet;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class HandleMDM
 * Record observation result set, message XML
 */
class HandleMDM extends RecordObservationResultSet
{
    /** @var string[] */
    static $event_codes = ['T02', 'T04', 'T10'];

    /**
     * Get data nodes
     *
     * @return array Get nodes
     */
    function getContentNodes()
    {
        $data = parent::getContentNodes();

        // COMMON_ORDER
        $common_orders = $this->queryNodes(
            "MDM_" . $this->_ref_exchange_hl7v2->code . ".COMMON_ORDER",
            null,
            $varnull
        );
        foreach ($common_orders as $_common_order) {
            // OBR - on récupère uniquement le OBR concernant le OBX
            $this->queryNode("OBR", $_common_order, $data, true);

            // ORC - Common Order [0-*]
            $this->queryNode("ORC", $_common_order, $data, true);
        }

        // TXA - Transcription Document Header
        $this->queryNode("TXA", null, $data, true);

        $tmp = [];
        // OBSERVATION
        $observations = $this->queryNodes(
            "MDM_" . $this->_ref_exchange_hl7v2->code . ".OBSERVATION",
            null,
            $varnull
        );
        foreach ($observations as $_observation) {
            // OBXs
            $this->queryNodes("OBX", $_observation, $tmp, true);
        }

        if ($tmp) {
            $data["observations"][] = $tmp;
        }

        return $data;
    }

    protected function handleObservations(array $data): void
    {
        /** @var array $observation */
        foreach ($data["observations"] as $key => $observation) {
            $observation['OBR'] = CMbArray::get($data, 'OBR');

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
