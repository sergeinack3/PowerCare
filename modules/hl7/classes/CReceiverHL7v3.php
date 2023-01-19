<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CClassMap;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CReport;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\CSourceSFTP;
use Ox\Interop\Hl7\Events\CHL7v3Event;
use Ox\Interop\Hl7\Events\SVS\CHL7v3AcknowledgmentSVS;
use Ox\Interop\Hl7\Events\XDSb\CHL7v3AcknowledgmentXDSb;
use Ox\Interop\Hl7\Events\XDSb\CHL7v3EventXDSbProvideAndRegisterDocumentSetRequest;
use Ox\Interop\Webservices\CSourceSOAP;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFileTraceability;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceFileSystem;
use Ox\Mediboard\System\CSourceHTTP;
use SoapFault;
use SoapVar;

/**
 * Class CReceiverHL7v3
 * Receiver HL7 v.3
 */
class CReceiverHL7v3 extends CInteropReceiver
{
    /** @var string[] */
    public const ACTORS_MANAGED = [
        self::ACTOR_DMP,
        self::ACTOR_ASIP,
        self::ACTOR_ZEPRA
    ];

    /** @var array Sources supportées par un destinataire */
    public static $supported_sources = [
        CSourceFTP::TYPE,
        CSourceSFTP::TYPE,
        CSourceSOAP::TYPE,
        CSourceHTTP::TYPE,
        CSourceFileSystem::TYPE,
    ];

    // DB Table key
    /** @var int */
    public $receiver_hl7v3_id;

    /** @var null */
    public $_i18n_code;

    /**
     * @inheritDoc
     */
    function getSpec()
    {
        $spec = parent::getSpec();
        $spec->table = 'receiver_hl7v3';
        $spec->key = 'receiver_hl7v3_id';
        $spec->messages = [
            "PRPA" => ["CPRPA"],
            "XDSb" => ["CXDSb"],
            "XDM" => ["CXXDM"],
            "SVS" => ["CSVS"],
            "PDQ" => ["CPDQ"],
        ];

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props = parent::getProps();
        $props["group_id"] .= " back|destinataires_HL7v3";

        return $props;
    }

    /**
     * Get event message
     *
     * @param string $profil Profil name
     *
     * @return mixed
     */
    function getEventMessage($profil)
    {
        if (!array_key_exists($profil, $this->_spec->messages)) {
            return;
        }

        return reset($this->_spec->messages[$profil]);
    }

    /**
     * Send the event ProvideAndRegisterDocumentSetRequest
     *
     * @param CDocumentItem $document_item Document
     *
     * @return mixed
     * @throws Exception
     */
    function sendEventProvideAndRegisterDocumentSetRequest(CDocumentItem $document_item)
    {
        $iti41 = new CHL7v3EventXDSbProvideAndRegisterDocumentSetRequest();
        if (!parent::sendEvent($iti41, $document_item)) {
            return;
        }

        $iti41->type = $this->libelle;

        $iti41->_event_name = "ProvideAndRegisterDocumentSetRequest";

        $headers = CHL7v3Adressing::createWSAddressing(
            "urn:ihe:iti:2007:ProvideAndRegisterDocumentSet-b",
            "http://ihexds.nist.gov/tf6/services/xdsrepositoryb"
        );

        try {
            $this->sendEvent($iti41, $document_item, $headers, true);
        } catch (CMbException $e) {
            $e->stepAjax(UI_MSG_WARNING);
        }
    }

    /**
     * @inheritdoc
     *
     * @param CHL7v3Event $event
     */
    public function sendEvent(
        $event,
        $object,
        $data = [],
        $headers = [],
        $message_return = false,
        $soapVar = false
    )
    {
        if (!parent::sendEvent($event, $object, $data, $headers, $message_return)) {
            return null;
        }

        $event->_receiver = $this;

        if (!$this->isMessageSupported(CClassMap::getSN($event))) {
            return false;
        }

        $this->loadConfigValues();
        $event->build($object);

        if (
            isset($event->report) && $event->report && $event->report->getItems()
            && isset($event->file_traceability) && $event->file_traceability
        ) {
            /** @var CReport $report */
            $report = $event->report;
            /** @var CFileTraceability $file_traceability */
            $file_traceability = $event->file_traceability;
            $file_traceability->report = $report->toJson();
            $file_traceability->attempt_sent++;
            $file_traceability->exchange_id = null;
            $file_traceability->setMsgError('DMP-msg-Error generation message');
            $file_traceability->store();

            // Suppression du flux généré en amont
            $exchange = $event->_exchange_hl7v3;
            $exchange->delete();
            CApp::rip();
        }

        $exchange = $event->_exchange_hl7v3;

        if (!$exchange->message_valide) {
            return null;
        }

        if (!$this->synchronous) {
            return null;
        }

        if ($message_return) {
            return $event->message;
        }

        $msg = $event->message;
        if ($soapVar) {
            $msg = trim(preg_replace("#^<\?xml[^>]*>#", "", $msg));
            $msg = new SoapVar($msg, XSD_ANYXML);
        }

        $source = CExchangeSource::get("$this->_guid-C{$event->event_type}");
        if (!$source->_id || !$source->active) {
            return null;
        }

        switch ($source->_class) {
            case "CSourceHTTP":
                /** @var CSourceHTTP $source */
                $uri = $source->getHost($event->_event_request);
                $client = $source->getClient();
                $response = $client->request('GET', $uri, ['query' => $object->_data]);
                $source->_acquittement = $response->getBody()->__toString();

                break;

            case "CSourceSFTP":
                $exchange->send_datetime = CMbDT::dateTime();
                $exchange->store();

                $source->setData($msg);
                $source->getClient()->send();
                break;

            default:
                /** @var CSourceSOAP $source */
                if ($headers) {
                    /** @var CSourceSOAP $source */
                    $soap_client = $source->getClient();
                    $soap_client->setHeaders($headers);
                }

                $source->setData($msg, null, $exchange);
                $exchange->send_datetime = CMbDT::dateTime();
                try {
                    if (!$source->evenement_name) {
                        $event_name = isset($event->_event_name) ? $event->_event_name : null;
                        $source->getClient()->send($event_name);
                    } else {
                        $source->getClient()->send();
                    }
                } catch (SoapFault $e) {
                    throw $e;
                } catch (Exception $e) {
                    throw $e;
                }
        }

        $exchange->response_datetime = CMbDT::dateTime();

        $ack_data = $source->getACQ();

        if (!$ack_data) {
            $exchange->store();

            return null;
        }

        if (!$ack = self::createAcknowledgment($event->event_type, $ack_data)) {
            $exchange->store();

            return null;
        }

        $exchange->send_datetime = CMbDT::dateTime();
        $exchange->statut_acquittement = $ack->getStatutAcknowledgment();
        $exchange->acquittement_valide = $ack->dom->schemafilename ?
            $ack->dom->schemaValidate() ? 1 : 0
            : 1;
        $exchange->_acquittement = $ack_data;
        $exchange->store();

        $ack->object = $object;
        $ack->_receiver = $this;
        $ack->_exchange_hl7v3 = $exchange;

        return $ack;
    }

    /**
     * Create the acknowledgment
     *
     * @param String $event_type evenment type
     * @param String $ack_data acknowledgment message
     *
     * @return CHL7v3AcknowledgmentPRPA|CHL7v3AcknowledgmentXDSb|CHL7v3AcknowledgmentSVS
     */
    static function createAcknowledgment($event_type, $ack_data)
    {
        $class_name = "C$event_type";

        return $class_name::getAcknowledgment($ack_data);
    }
}
