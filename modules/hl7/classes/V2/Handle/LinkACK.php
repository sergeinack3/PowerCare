<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle;

use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2MessageXML;

/**
 * Class LinkACK
 * Link ACK message XML HL7
 */
class LinkACK extends CHL7v2MessageXML
{
    /**
     * @inheritdoc
     */
    function handle(CHL7Acknowledgment $ack = null, CMbObject $object = null, $data = [])
    {
        $data = array_merge($data, $this->getContentNodes());

        // Récupération de l'identifiant de l'échange HL7v2
        $MSA_1 = $this->queryTextNode("MSA.1", $data["MSA"]);
        $MSA_2 = $this->queryTextNode("MSA.2", $data["MSA"]);

        if (!$MSA_2) {
            return;
        }

        $exchange_hl7v2 = new CExchangeHL7v2();
        $exchange_hl7v2->load($MSA_2);

        $exchange_hl7v2->statut_acquittement = $MSA_1;
        $exchange_hl7v2->acquittement_valide = 1;
        $exchange_hl7v2->_acquittement       = CMbArray::get($data, "msg_hl7");
        $exchange_hl7v2->store();
    }

    /**
     * @inheritdoc
     */
    function getContentNodes()
    {
        $data = [];

        $this->queryNode("MSA", null, $data, true);

        return $data;
    }
}
