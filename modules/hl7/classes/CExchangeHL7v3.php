<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Interop\Eai\CEchangeXML;
use Ox\Interop\Eai\CInteropActor;

/**
 * Class CExchangeHL7v3
 * Exchange HL7v3
 */
class CExchangeHL7v3 extends CEchangeXML
{
    static $messages = [
        "PRPA" => "CPRPA",
        "XDSb" => "CXDSb",
        "SVS"  => "CSVS",
        "SVS"  => "CSVS",
        "PDQ"  => "CPDQ",
        "XDM"  => "CXDM",
    ];

    // DB Table key
    public $exchange_hl7v3_id;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->loggable = false;
        $spec->table    = 'exchange_hl7v3';
        $spec->key      = 'exchange_hl7v3_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                  = parent::getProps();
        $props["receiver_id"]   = "ref class|CReceiverHL7v3 autocomplete|nom back|echanges";
        $props["initiateur_id"] = "ref class|CExchangeHL7v3 back|notifications";
        $props["object_class"]  = "enum list|CSejour|CPatient|CConsultation|CCompteRendu|CFile show|0";
        $props["object_id"]     .= " back|exchanges_hl7v3";
        $props["group_id"]      .= " back|exchanges_hl7v3";

        $props['sender_id']               .= ' back|expediteur_hl7v3';
        $props['message_content_id']      .= ' back|messages_hl7v3';
        $props['acquittement_content_id'] .= ' back|acquittements_hl7v3';

        return $props;
    }

    /**
     * @see parent::loadRefsBack()
     */
    function loadRefsBack()
    {
        parent::loadRefsBack();

        $this->loadRefNotifications();
    }

    /**
     * @see parent::loadRefNotifications()
     */
    function loadRefNotifications()
    {
        $this->_ref_notifications = $this->loadBackRefs("notifications");
    }

    /**
     * @see parent::understand()
     */
    public function understand(string $data, CInteropActor $actor = null): bool
    {
        if (!$dom = $this->isWellFormed($data)) {
            return false;
        }

        /** @todo à faire */
        return false;
    }

    /**
     * Check if data is well formed
     *
     * @param string        $data  Data
     * @param CInteropActor $actor Actor
     *
     * @return CHL7v3MessageXML|null
     */
    function isWellFormed($data, CInteropActor $actor = null)
    {
        $dom = new CHL7v3MessageXML();
        if ($dom->loadXML($data, LIBXML_NOWARNING | LIBXML_NOERROR) !== false) {
            return $dom;
        }

        return null;
    }

    /**
     * @see parent::handle()
     */
    function handle()
    {
        $operator_hl7v3 = new COperatorHL7v3();

        return $operator_hl7v3->event($this);
    }

    /**
     * @see parent::getFamily()
     */
    function getFamily()
    {
        return self::$messages;
    }
}
