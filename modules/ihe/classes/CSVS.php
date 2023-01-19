<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\Core\CMbObject;
use Ox\Interop\Eai\CEAIException;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Hl7\CHL7v3Adressing;
use Ox\Interop\Hl7\CHL7v3MessageXML;
use Ox\Interop\Hl7\CReceiverHL7v3;
use Ox\Interop\Hl7\Events\CHL7v3Event;
use Ox\Interop\Hl7\Events\SVS\CHL7v3AcknowledgmentSVS;
use Ox\Mediboard\Etablissement\CGroups;
use SoapFault;

/**
 * Class CSVS
 * Sharing Value Sets
 */
class CSVS extends CIHE
{
    /**
     * @var array
     */
    public static $transaction_iti48 = [
        "RetrieveValueSet",
    ];

    /**
     * @var array
     */
    public static $transaction_iti60 = [
        "RetrieveMultipleValueSets",
    ];

    /**
     * @var array
     */
    public static $evenements = [
        // ITI-48
        "RetrieveValueSet"          => "CHL7v3EventSVSRetrieveValueSet",

        // ITI-60
        "RetrieveMultipleValueSets" => "CHL7v3EventSVSRetrieveMultipleValueSets",
    ];

    /**
     * Construct
     *
     * @return CSVS
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_ITI;
        $this->type   = "SVS";

        $this->_categories = [
            "ITI-48" => self::$transaction_iti48,
            "ITI-60" => self::$transaction_iti60,
        ];

        parent::__construct();
    }

    /**
     * @see parent::getEvenements
     */
    public function getEvenements(): ?array
    {
        return self::$evenements;
    }

    /**
     * @see parent::getVersions
     */
    public function getVersions(): ?array
    {
        return self::$versions;
    }

    /**
     * Retrieve transaction name,
     *
     * @param string $code Event code
     *
     * @return string Transaction name
     */
    public static function getTransaction(string $code): ?string
    {
        if (in_array($code, self::$transaction_iti48)) {
            return "ITI48";
        }

        if (in_array($code, self::$transaction_iti60)) {
            return "ITI60";
        }

        return null;
    }

    /**
     * Return data format object
     *
     * @param CExchangeDataFormat $exchange Instance of exchange
     *
     * @return object An instance of data format
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
        $classname = "CHL7v3Event{$exchange->type}{$exchange->sous_type}";

        return new $classname();
    }

    /**
     * Get aAcknowledgment object
     *
     * @param string $ack_data Data
     *
     * @return CHL7v3AcknowledgmentSVS|null
     */
    public static function getAcknowledgment(string $ack_data): ?CHL7v3AcknowledgmentSVS
    {
        $dom = new CHL7v3MessageXML();
        $dom->loadXML($ack_data);
        $dom->formatOutput = true;

        $acknowledgment_svs      = new CHL7v3AcknowledgmentSVS();
        $acknowledgment_svs->dom = $dom;

        return $acknowledgment_svs;
    }

    /**
     * Send retrieve valueSet event
     *
     * @param string $OID
     * @param string $version
     * @param string $language
     *
     * @return array|string|string[]|null
     * @throws CEAIException
     * @throws SoapFault
     */
    public static function sendRetrieveValueSet(
        string $OID,
        ?string $version = null,
        ?string $language = null
    ) {
        $receiver_hl7v3           = (new CInteropActorFactory())->receiver()->makeHL7v3();
        $receiver_hl7v3->actif    = 1;
        $receiver_hl7v3->group_id = CGroups::loadCurrent()->_id;

        /** @var CReceiverHL7v3[] $receivers */
        $receivers = $receiver_hl7v3->loadMatchingList();

        $event_name = "CHL7v3EventSVSRetrieveValueSet";

        /** @var CHL7v3Event $event */
        $event = new $event_name();
        $event->_event_name = "ValueSetRepository_RetrieveValueSet";

        $data = [
            "id"      => trim($OID),
            "version" => trim($version),
            "lang"    => trim($language),
        ];

        $object        = new CMbObject();
        $object->_data = $data;

        $headers = CHL7v3Adressing::createWSAddressing(
            "urn:ihe:iti:2008:RetrieveValueSet",
            "http://valuesetrepository/"
        );

        $value_set = null;
        foreach ($receivers as $_receiver) {
            if (!$_receiver->isMessageSupported($event_name)) {
                continue;
            }

            $value_set = $_receiver->sendEvent($event, $object, $headers, true)->getQueryAck();
        }

        return $value_set;
    }
}
